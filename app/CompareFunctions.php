<?php
use App\LongReservation;
use App\LongReservationDate;
use App\TemporaryReservation;
use App\ManualReservation;

function getTimeQuery(){
    return "((from_time BETWEEN ? AND ? AND from_time != ?) OR (to_time
                BETWEEN ? AND ? AND to_time != ?) OR
              (? BETWEEN from_time AND to_time AND ? != to_time) OR
              (? BETWEEN from_time AND to_time AND ? != from_time))";
}

/** Functions for long reservations
 * @param $from_date
 * @param $to_date
 * @param $long
 * @param $day_of_week
 * @param $places
 * @param $from_time
 * @param $to_time
 * @return LongReservation[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
 */
function compareLongWithLong($from_date, $to_date, $long, $day_of_week, $places, $from_time, $to_time){
  return LongReservation::with(["longReservationDates", "reservation",
                                "longReservationDates.longReservationPlaces.floor",
                                "longReservationDates.longReservationPlaces.room"])
              ->where([["from_date", ">=", $from_date], ["from_date", "<=", $to_date]])
              ->orWhere([["to_date", ">=", $from_date], ["to_date", "<=", $to_date]])
              ->orWhere([["from_date", "<", $from_date], ["to_date", ">", $to_date]])
              ->get()
              ->reject(function($value) use ($long, $day_of_week, $places, $from_time, $to_time, $from_date, $to_date){
                if($value->reservation->is_approved != 1 || ($long && ($value->id == $long->id ||
                            ($long->reservation->isEditRequest &&
                                $value->reservation->id == $long->reservation->isEditRequest->reservation->id)))){
                  return true;
                }
                if(isReservationOver($value->reservation) || isInsidePausePeriod($value->reservation, $value->from_date, $value->to_date,
                    $from_date, $to_date, true, $day_of_week, $places)){
                    return true;
                }
                $whereStr = "day_of_week = ? AND long_reservation_id = ?";
                $whereArr = [$day_of_week, $value->id];
                $lrd = $value->longReservationDates()->whereRaw($whereStr.' AND '.getTimeQuery(),
                                                                  array_merge($whereArr, [$from_time, $to_time, $to_time,
                                                                  $from_time, $to_time, $from_time, $from_time, $from_time,
                                                                  $to_time, $to_time]))
                                                      ->get();
                $lrd = $lrd->reject(/**
                 * @param LongReservationDate $d
                 * @return bool
                 */
                    function($d) use ($places) {
                    $dps = $d->longReservationPlaces()->get();
                    foreach($places as $place){
                        $dp = $dps->where('floor_id', $place["floor"]->id);
                        if($place["room"]){
                            $dp = $dp->where('room_id', $place["room"]->id);
                        }
                        $dp = collect($dp->all());
                        if($dp->count()){
                            return false;
                        }
                    }
                    return true;
                });
                return $lrd->count() == 0;
              });
}

function compareLongWithTemp($from_date, $to_date, $day_of_week, $places, $from_time, $to_time){
  return TemporaryReservation::with(["temporaryReservationDates", "temporaryReservationPlaces",
             "reservation"])->get()
            ->reject(function($value) use ($day_of_week, $places, $from_time, $to_time,
                                                  $from_date, $to_date){
              if($value->reservation->is_approved != 1 || isReservationOver($value->reservation)){
                return true;
              }
              $trd = $value->temporaryReservationDates()->whereRaw('temporary_reservation_id = ? AND
                                                                date BETWEEN ? AND ? AND '.getTimeQuery(),
                                                                [$value->id, $from_date, $to_date,
                                                                $from_time, $to_time, $to_time,
                                                                $from_time, $to_time, $from_time,
                                                                $from_time, $from_time, $to_time, $to_time])
                                                        ->get();
              $trd = $trd->reject(function($d) use ($to_date, $from_date, $day_of_week, $places){
                    $differentDays = getDay($d->date) != $day_of_week;
                  return $differentDays ||
                    (!$differentDays && isInsidePausePeriod($d->temporaryReservation->reservation, $d->date,
                            $d->date, $from_date, $to_date, true, $day_of_week, $places));
              });
              if($trd->count() == 0){
                return true;
              }
              foreach($places as $place){
                  $trp = $value->temporaryReservationPlaces()->where("floor_id", $place['floor']->id);
                  if($place['room']){
                      $trp = $trp->where("room_id", $place['room']->id);
                  }
                  $trp = $trp->get();
                  if($trp->count()){
                      return false;
                  }
              }
              return true;
            });
}

function compareLongWithManual($from_date, $to_date, $day_of_week, $places, $from_time, $to_time){
    return ManualReservation::with(["manualReservationsDates", "manualPlace"])->get()
          ->reject(function($value) use ($from_date, $to_date, $day_of_week, $places, $from_time, $to_time){
              if(isReservationOver($value)) {
                  return true;
              }
            $fl = $value->manualPlace->floor()->withTrashed()->first();
            $rm = $value->manualPlace->room()->withTrashed()->first();
            $hasPlace = false;
            foreach($places as $place){
                if($fl->id === $place["floor"]->id &&
                      (($rm && $place["room"] && $rm->id === $place["room"]->id) ||
                          (!$rm && !$place["room"]))){
                    $hasPlace = true;
                      break;
                }
            }
            if(!$hasPlace){
                  return true;
            }
            $mrd = $value->manualReservationsDates()->whereRaw('manual_reservation_id = ? AND date BETWEEN ? AND ? AND '.getTimeQuery(),
                                                              [$value->id, $from_date, $to_date,
                                                              $from_time, $to_time, $to_time,
                                                              $from_time, $to_time, $from_time,
                                                              $from_time, $from_time,
                                                              $to_time, $to_time])
                                                      ->get();
            $mrd = $mrd->reject(function($d) use ($to_date, $from_date, $value, $day_of_week, $places){
                $differentDays = getDay($d->date) != $day_of_week;
                return $differentDays ||
                    (!$differentDays && isInsidePausePeriod($value, $d->date,
                            $d->date, $from_date, $to_date, true, $day_of_week, $places));
            });
            if($mrd->count() == 0){
              return true;
            }
            return false;
          });
}

function getOtherReservationsForLong(&$other_reservations, $day, $from_date, $to_date, $long, $day_of_week, $places, $from_time, $to_time){
    //format dates
    $from_date = format_reversed_date($from_date);
    $to_date = format_reversed_date($to_date);
    $long_res = compareLongWithLong($from_date, $to_date, $long, $day_of_week, $places, $from_time, $to_time);
    if($long_res->count() > 0){
        $long_res->map(function ($item) use (&$other_reservations, $day) {
            $other_reservations[$day]["reservations"][] = $item;
        });
    }
    $temp_res = compareLongWithTemp($from_date, $to_date, $day_of_week, $places, $from_time, $to_time);
    if($temp_res->count() > 0){
        $temp_res->map(function ($item) use (&$other_reservations, $day) {
            $other_reservations[$day]["reservations"][] = $item;
        });
    }

    $manualRes = compareLongWithManual($from_date, $to_date, $day_of_week, $places, $from_time, $to_time);
    if($manualRes->count() > 0){
        $manualRes->map(function ($item) use (&$other_reservations, $day) {
            $other_reservations[$day]["manual_reservations"][] = $item;
        });
    }
}


/** Functions for temporary reservations
 * @param $date
 * @param $places
 * @param $from_time
 * @param $to_time
 * @return LongReservation[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
 */
function compareTempWithLong($date, $places, $from_time, $to_time){
    $day = getDay($date);
    return LongReservation::with(["longReservationDates", "reservation",
                                    "longReservationDates.longReservationPlaces",
                                    "longReservationDates.longReservationPlaces.floor",
                                    "longReservationDates.longReservationPlaces.room"])
              ->where([["from_date", "<=", $date], ["to_date", ">=", $date]])
              ->get()
              ->reject(function($value) use ($from_time, $to_time, $date, $places, $day){
                if($value->reservation->is_approved != 1 || isReservationOver($value->reservation)){
                  return true;
                }
                $lrd = $value->longReservationDates()->whereRaw('long_reservation_id = ? AND day_of_week = ? AND '.getTimeQuery(),
                                                                  [$value->id, $day,
                                                                  $from_time, $to_time, $to_time,
                                                                  $from_time, $to_time, $from_time,
                                                                  $from_time, $from_time, $to_time, $to_time])
                                                          ->get();
                $noMatchingPlaces = true;
                  foreach($lrd as $long_rd){
                      if(isInsidePausePeriod($value->reservation, $value->from_date, $value->to_date,
                          $date, $date, false, $long_rd->day_of_week, $places)){
                          continue;
                      }

                      foreach($places as $placeArr){
                          foreach($long_rd->longReservationPlaces()->get() as $place){
                              $fl = $place->floor()->withTrashed()->first();
                              $rm = $place->room()->withTrashed()->first();
                              if($fl && $fl->id == $placeArr["floor"]->id){
                                  if($rm && $placeArr["room"]){
                                      $noMatchingPlaces = !($rm->id == $placeArr["room"]->id);
                                      if(!$noMatchingPlaces){
                                          return false;
                                      }
                                  }
                                  elseif(!$rm && !$placeArr["room"]){
                                      return false;
                                  }
                              }
                          }
                      }
                  }
                return $noMatchingPlaces;
              });
}

function compareTempWithTemp($date, $temp, $places, $from_time, $to_time){
    return TemporaryReservation::with(["temporaryReservationDates", "temporaryReservationPlaces",
             "reservation"])->get()
            ->reject(function($value) use ($temp, $from_time, $to_time, $date, $places){
              if($value->reservation->is_approved != 1 || ($temp && ($value->id == $temp->id ||
                          ($temp->reservation->isEditRequest &&
                              $value->reservation->id == $temp->reservation->isEditRequest->reservation->id)))){
                  return true;
              }
              if(isReservationOver($value->reservation)) {
                  return true;
              }
              $trd = $value->temporaryReservationDates()->whereRaw('temporary_reservation_id = ? AND date = ? AND '.getTimeQuery(),
                                                                [$value->id, $date,
                                                                $from_time, $to_time, $to_time,
                                                                $from_time, $to_time, $from_time,
                                                                $from_time, $from_time, $to_time, $to_time])
                                                        ->get();
              $trd = $trd->reject(function($d) use ($value, $date, $places){
                  return isInsidePausePeriod($value->reservation, $d->date, $d->date, $date, $date, false, null, $places);
              });
              if(!$trd->count()){
                  return true;
              }
              foreach($places as $place){
                  if($place["floor"]){
                    $trp = $value->temporaryReservationPlaces()->where("floor_id", $place["floor"]->id);
                    if($place["room"]){
                        $trp = $trp->where("room_id", $place["room"]->id);
                    }
                    $trp = $trp->get();
                    if($trp->count() > 0){
                        return false;
                    }
                  }
              }
              return true;
            });
}

function compareTempWithManual($date, $places, $from_time, $to_time){
    return ManualReservation::with(["manualReservationsDates", 'manualPlace'])
                        ->get()
                        ->reject(function($value) use ($from_time, $to_time, $date, $places){
                            if(isReservationOver($value)) {
                                return true;
                            }
                            $hasPlace = false;
                            foreach($places as $place){
                                $fl = $value->manualPlace->floor;
                                $rm = $value->manualPlace->room;
                              if($place["floor"]->id == $fl->id &&
                                  (($place["room"] && $rm &&
                                    $place["room"]->id == $rm->id) ||
                                    (!$place["room"] && !$rm))){
                                    $hasPlace = true;
                                    break;
                                  }
                            }
                            if(!$hasPlace){
                                return true;
                            }
                            $dates = $value->manualReservationsDates()->whereRaw('manual_reservation_id = ? AND date = ? AND '.getTimeQuery(),
                                                                              [$value->id, $date,
                                                                              $from_time, $to_time, $to_time,
                                                                              $from_time, $to_time, $from_time,
                                                                              $from_time, $from_time, $to_time, $to_time])
                                                                      ->get();
                            $dates = $dates->reject(function($d) use ($value, $date, $places){
                               return isInsidePausePeriod($value, $d->date, $d->date,
                                   $date, $date, false, null, $places);
                            });
                            return $dates->count() == 0;
                        });
}

function getOtherReservationsForTemp(&$other_reservations, $i, $date, $temp, $places, $from_time, $to_time)
{
    $date = format_reversed_date($date);
    $long_res = compareTempWithLong($date, $places, $from_time, $to_time);
    if ($long_res->count() > 0) {
        $long_res->map(function ($item) use (&$other_reservations, $i) {
            $other_reservations[$i]["reservations"][] = $item;
        });
    }
    $temp_res = compareTempWithTemp($date, $temp, $places, $from_time, $to_time);
    if ($temp_res->count() > 0) {
        $temp_res->map(function ($item) use (&$other_reservations, $i) {
            $other_reservations[$i]["reservations"][] = $item;
        });
    }
    $manualRes = compareTempWithManual($date, $places, $from_time, $to_time);
    if ($manualRes->count() > 0) {
        $manualRes->map(function ($item) use (&$other_reservations, $i) {
            $other_reservations[$i]["manual_reservations"][] = $item;
        });
    }
}

/** Functions for manual reservations
 * @param $date
 * @param $place
 * @param $from_time
 * @param $to_time
 * @return LongReservation[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
 */
function compareManualWithLong($date, $place, $from_time, $to_time){
    $day = getDay($date);
    return LongReservation::with(["longReservationDates", "reservation"])
          ->where([["from_date", "<=", $date], ["to_date", ">=", $date]])
          ->get()
          ->reject(function($value) use ($from_time, $to_time, $date, $place, $day){
            if($value->reservation->is_approved != 1 || isReservationOver($value->reservation)){
              return true;
            }
            $whereStr = "long_reservation_id = ? AND day_of_week = ?";
            $whereArr = [$value->id, $day];
            $lrd = $value->longReservationDates()->whereRaw($whereStr.' AND '.getTimeQuery(),
                                                            array_merge($whereArr, [$from_time, $to_time, $to_time,
                                                                $from_time, $to_time, $from_time, $from_time, $from_time,
                                                                $to_time, $to_time]))
                                                            ->get();
            foreach($lrd as $long_rd){
                if(isInsidePausePeriod($value->reservation, $value->from_date, $value->to_date,
                    $date, $date, false, $long_rd->day_of_week, [$place])){
                    return true;
                }
                foreach($long_rd->longReservationPlaces()->get() as $longPlace){
                    $floor = $longPlace->floor()->withTrashed()->first();
                    $room = $longPlace->room()->withTrashed()->first();
                    if($floor->id === $place["floor"]->id && (($room && $place["room"] && $room->id === $place["room"]->id) ||
                            (!$room && !$place["room"]))){
                        return false;
                    }
                }
            }
            return true;
          });
}

function compareManualWithTemp($date, $place, $from_time, $to_time){
    return TemporaryReservation::with(["temporaryReservationDates", "temporaryReservationPlaces",
           "reservation"])->get()
          ->reject(function($value) use ($from_time, $to_time, $date, $place){
            if($value->reservation->is_approved != 1 || isReservationOver($value->reservation)){
              return true;
            }
            $where = [["floor_id", $place["floor"]->id],["temporary_reservation_id", $value->id]];
            if($place["room"]){
              $where[] = ["room_id", $place["room"]->id];
            }
            $trp = $value->temporaryReservationPlaces()->where($where)
                                                      ->get();
            if(!$trp->count()){
              return true;
            }
            $trd = $value->temporaryReservationDates()->whereRaw('temporary_reservation_id = ? AND date = ? AND '.getTimeQuery(),
                                                                [$value->id, $date, $from_time, $to_time, $to_time,
                                                                    $from_time, $to_time, $from_time,
                                                                    $from_time, $from_time, $to_time, $to_time])
                                                        ->get();
            $trd = $trd->reject(function($d) use ($value, $date, $place){
               return isInsidePausePeriod($value->reservation, $d->date, $d->date, $date, $date,
                   false, null, [$place]);
            });
            return $trd->count() == 0;
          });
}

function compareManualWithManual($date, $id, $place, $from_time, $to_time){
    return ManualReservation::with(["manualReservationsDates", 'manualPlace'])
                    ->get()
                    ->reject(function($value) use ($from_time, $to_time, $date, $place, $id){
                        if(($id && $value->id == $id) || isReservationOver($value)){
                            return true;
                        }
                        $fl = $value->manualPlace->floor()->withTrashed()->first();
                        $rm = $value->manualPlace->room()->withTrashed()->first();
                        if(($fl && $place["floor"] && $fl->id != $place["floor"]->id) ||
                            ($rm && $place["room"] && $rm->id != $place["room"]->id) ||
                            (!$rm && $place["room"]) || ($rm && !$place["room"])){
                            return true;
                        }
                        $dates = $value->manualReservationsDates()->whereRaw('manual_reservation_id = ? AND date = ? AND '.getTimeQuery(),
                                                                    [$value->id, $date, $from_time, $to_time, $to_time,
                                                                        $from_time, $to_time, $from_time, $from_time,
                                                                        $from_time, $to_time, $to_time])
                                                                    ->get();
                    $dates = $dates->reject(function($d) use ($value, $date, $place){
                       return isInsidePausePeriod($value, $d->date, $d->date, $date, $date, false, null,
                           [$place]);
                    });
                      return $dates->count() == 0;
                    });
}

function getOtherReservationsForManual(&$other_reservations, $i, $date, $id, $place, $from_time, $to_time){
    $date = format_reversed_date($date);
    $long_res = compareManualWithLong($date, $place, $from_time, $to_time);
    if($long_res->count() > 0){
        $long_res->map(function ($item) use (&$other_reservations, $i) {
            $other_reservations[$i]["reservations"][] = $item;
        });
    }
    $temp_res = compareManualWithTemp($date, $place, $from_time, $to_time);
    if($temp_res->count() > 0){
    $temp_res->map(function ($item) use (&$other_reservations, $i) {
        $other_reservations[$i]["reservations"][] = $item;
    });
    }
    $manualRes = compareManualWithManual($date, $id, $place, $from_time, $to_time);
    if($manualRes->count() > 0){
        $manualRes->map(function ($item) use (&$other_reservations, $i) {
            $other_reservations[$i]["manual_reservations"][] = $item;
        });
    }
}