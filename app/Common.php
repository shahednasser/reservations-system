<?php
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
function get_day_times(){
  return ["00:00", "00:15", "00:30", "00:45", "01:00", "01:15", "01:30", "01:45", "02:00", "02:15", "02:30",
          "02:45", "03:00", "03:15", "03:30", "03:45", "04:00", "04:15", "04:30", "04:45", "05:00", "05:15",
          "05:30", "05:45", "06:00", "06:15", "06:30", "06:45", "07:00", "07:15", "07:30", "07:45",
          "08:00", "08:15", "08:30", "08:45", "09:00", "09:15", "09:30", "09:45", "10:00", "10:15",
          "10:30", "10:45", "11:00", "11:15", "11:30", "11:45", "12:00", "12:15", "12:30", "12:45",
          "13:00", "13:15", "13:30", "13:45", "14:00", "14:15", "14:30", "14:45", "15:00", "15:15",
          "15:30", "15:45", "16:00", "16:15", "16:30", "16:45", "17:00", "17:15", "17:30", "17:45",
          "18:00", "18:15", "18:30", "18:45", "19:00", "19:15", "19:30", "19:45", "20:00", "20:15",
          "20:30", "20:45", "21:00", "21:15", "21:30", "21:45", "22:00", "22:15", "22:30", "22:45",
          "23:00", "23:15", "23:30", "23:55"];
}

function get_remaining_day_times(){
  $hour = intval(date_format(Carbon::now(), "H"));
  $times = [];
  for($i = $hour; $i <= 23; $i++){
    $times[] = $i.":00";
    $times[] = $i.":15";
    $times[] = $i.":30";
  }
  return $times;
}

function format_datetime($datetime){
  return date_format(date_create($datetime), "Y-m-d H:i:s");
}

function format_date($datetime){
  return date_format(date_create($datetime), "d/m/Y");
}

function format_db_date($datetime){
    return date_format(date_create($datetime), "Y-m-d");
}

function format_reversed_date($datetime){
    $dt = date_create_from_format('d/m/Y', $datetime);
    if(!$dt){
        return $datetime;
    }
    return date_format($dt, 'Y-m-d');
}

function format_time($datetime){
  return date_format(date_create($datetime), "H:i:s");
}

function format_time_without_seconds($datetime){
  return date_format(date_create($datetime), "H:i");
}

function getDay($datetime){
  return intval(date_format(date_create($datetime), "w"));
}

function validateTime($time){
  $arr = explode(":", $time);
  if(count($arr) != 2 || strlen($arr[0]) != 2 || strlen($arr[1]) != 2){
    return false;
  }

  $hour = intval($arr[0]);
  $minute = intval($arr[1]);
  if($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59){
    return false;
  }

  return true;
}

function validateFromTime($from_time, $to_time){
  return $from_time < $to_time;
}

function paginate($items, $perPage = 15, $path = null) {
  //get current page from url
  $currentPage = LengthAwarePaginator::resolveCurrentPage();

  //Slice the items
  $currentItems = $items->slice(($currentPage - 1) * $perPage, $perPage);

  $options = [];
  if($path) {
      $options["path"] = $path;
  }

  //return paginator
  return new LengthAwarePaginator($currentItems, count($items), $perPage, null, $options);
}

function is12AM($time){
  $timeArr = explode(":", $time);
  $zeros = true;
  foreach($timeArr as $value){
    if(intval($value) != 0){
      $zeros = false;
      break;
    }
  }
  return $zeros;
}

function sendPushNotification($title, $message, $url, $subscriber){

  $apiKey = config('app.pushalert_api');

  $curlUrl = "https://api.pushalert.co/rest/v1/send";

  //POST variables
  $post_vars = array(
    "title" => $title,
    "message" => $message,
    "url" => $url,
    "subscriber" => $subscriber,
  );

  $headers = Array();
  $headers[] = "Authorization: api_key=".$apiKey;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $curlUrl);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_vars));
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);

  $output = json_decode($result, true);
  if($output["success"]) {
    return true; //Sent Notification ID
  }
  else {
    //Others like bad request
    return false;
  }
}

function timeBetween($from_time_1, $from_time_2, $to_time_1, $to_time_2){
    $ft_1 = \Carbon\Carbon::createFromTimeString($from_time_1);
    $tt_1 = \Carbon\Carbon::createFromTimeString($to_time_1);
    $ft_2 = \Carbon\Carbon::createFromTimeString($from_time_2);
    $tt_2 = \Carbon\Carbon::createFromTimeString($to_time_2);
    return ($ft_1->between($ft_2, $tt_2) && $ft_1->notEqualTo($tt_2)) ||
            ($ft_2->between($ft_1, $tt_1) && $ft_2->notEqualTo($tt_1)) ||
            ($tt_1->between($ft_2, $tt_2) && $tt_1->notEqualTo($ft_2)) ||
            ($tt_2->between($ft_1, $tt_1) && $tt_2->notEqualTo($ft_1));
}

function dateBetween($from_date_1, $from_date_2, $to_date_1, $to_date_2){
    $fd_1 = createDate($from_date_1);
    $td_1 = createDate($to_date_1);
    $fd_2 = createDate($from_date_2);
    $td_2 = createDate($to_date_2);
    return $fd_1->between($fd_2, $td_2) ||
            $fd_2->between($fd_1, $td_1) ||
            $td_1->between($fd_2, $td_2) ||
            $td_2->between($fd_1, $td_1);
}

function dateBetweenForPaused($from_date_1, $from_date_2, $to_date_1, $to_date_2, $isFirstSection = false){
    $fd_1 = createDate($from_date_1);
    $td_1 = createDate($to_date_1);
    $fd_2 = createDate($from_date_2);
    $td_2 = createDate($to_date_2);
    if($isFirstSection){
        return ($fd_1->between($fd_2, $td_2) && $fd_1->notEqualTo($td_2)) ||
            ($fd_2->between($fd_1, $td_1)) ||
            ($td_1->between($fd_2, $td_2) && $td_1->notEqualTo($td_2)) ||
            ($td_2->between($fd_1, $td_1) && $td_2->notEqualTo($fd_1) && $td_2->notEqualTo($td_1));
    }
    return ($fd_1->between($fd_2, $td_2) && $fd_1->notEqualTo($fd_2)) ||
        ($fd_2->between($fd_1, $td_1) && $fd_2->notEqualTo($td_1) && $fd_2->notEqualTo($fd_1)) ||
        ($td_1->between($fd_2, $td_2) && $td_1->notEqualTo($fd_2)) ||
        ($td_2->between($fd_1, $td_1) && $td_2->notEqualTo($fd_1));
}

function dateEqual($date_1, $date_2){
    $date1 = createDate($date_1);
    $date2 = createDate($date_2);
    return $date1->toDateString() === $date2->toDateString();
}

/**
 * @param $date
 * @return Carbon
 */
function createDate($date, $zeroTime = false){
    $date_obj = date_create($date);
    $d = \Carbon\Carbon::createFromDate(date_format($date_obj, 'Y'),
        date_format($date_obj, 'm'), date_format($date_obj, 'd'));
    if($zeroTime) {
        $d->setTime(0, 0, 0);
    }
    return $d;
}

function isBefore($date_1, $date_2) {
    $date1 = createDate($date_1);
    $date2 = createDate($date_2);
    return $date1->isBefore($date2) && !dateEqual($date_1, $date2);
}

function isAfter($date_1, $date_2) {
    $date1 = createDate($date_1);
    $date2 = createDate($date_2);
    return $date1->isAfter($date2) && !dateEqual($date_1, $date2);
}

function isInsidePausePeriod($reservation, $reservation_from_date, $reservation_to_date, $from_date, $to_date,
                             $isLong = false, $day_of_week = null, $places = []){
    //check if reservation is paused
    //new reservation needs to be between start of the reservation and start of
    //paused period or between end of paused period and end of reservation to be
    //taken into consideration
    //paused reservation from_time should not be equal to from_time of reservation
    //same for to time
//    $isInsidePausePeriod =  $reservation->pausedReservation &&
//        ((isPausedOnDay($reservation->pausedReservation->from_date, $from_date) &&
//                    dateEqual($reservation->pausedReservation->to_date, $to_date)) ||
//                (dateEqual($reservation->pausedReservation->to_date, $reservation_to_date) ||
//                    !dateBetweenForPaused($from_date, $reservation->pausedReservation->to_date,
//                        $to_date, $reservation_to_date))
//            && (dateEqual($reservation->pausedReservation->from_date, $reservation_from_date) ||
//                !dateBetweenForPaused($from_date, $reservation_from_date, $to_date,
//                    $reservation->pausedReservation->from_date, true)));
//    if(!$isLong && $reservation->pausedReservation){
//        if((isPausedOnDay($reservation, $from_date) || isPausedOnDay($reservation, $to_date)) &&
//            isPausedInPlaces($reservation->pausedReservation->pausedReservationPlaces()->get(), $places)){
//            return true;
//        }
//    }
    if(!$isLong) {
        return $reservation->pausedReservation &&
            (isPausedOnDay($reservation, $from_date) || isPausedOnDay($reservation, $to_date)) &&
            isPausedInPlaces($reservation->pausedReservation->pausedReservationPlaces()->get(), $places);
    }
    $isInsidePausePeriod = $reservation->pausedReservation && ((isPausedOnDay($reservation, $from_date) ||
            isPausedOnDay($reservation, $to_date)) &&
            (($reservation->pausedReservation->from_date === $reservation->pausedReservation->to_date &&
                    (!isAfter($reservation_to_date, $reservation->pausedReservation->from_date) &&
                        !isBefore($reservation_from_date, $reservation->pausedReservation->to_date))) ||
                ($reservation->pausedReservation->from_date !== $reservation->pausedReservation->to_date &&
                    !isBefore($reservation_to_date, $reservation->pausedReservation->from_date)  &&
                    !isAfter($reservation_from_date, $reservation->pausedReservation->to_date) &&
                    !isBefore($reservation_from_date, $reservation->pausedReservation->from_date) &&
                    !isAfter($reservation_to_date, $reservation->pausedReservation->to_date))));
    if(!$isInsidePausePeriod && $reservation->pausedReservation){
        if(dateEqual($reservation->pausedReservation->from_date, $reservation->pausedReservation->to_date) &&
            dateEqual($reservation_from_date, $reservation_to_date) &&
            !dateEqual($reservation->pausedReservation->from_date, $reservation_from_date)){
            if(($reservation_from_date < $reservation->pausedReservation->from_date &&
                    $reservation_to_date < $reservation->pausedReservation->from_date) ||
                ($reservation_from_date > $reservation->pausedReservation->to_date &&
                    $reservation_to_date > $reservation->pausedReservation->to_date)){
                return $isInsidePausePeriod;
            }
            return isPausedInPlaces($reservation->pausedReservation->pausedReservationPlaces()->get(), $places);
        }
        $hasDaysIn = false;
        //get diff for period before pause period
        //get diff between pause start date and new reservation start date
        $diff_1 = getDaysDiff($from_date, $reservation->pausedReservation->from_date);
        if($diff_1 > 0){
            //check if new reservation date is before reservation date
            if($from_date < $reservation_from_date){
                $diff = getDaysDiff($reservation_from_date, $reservation->pausedReservation->from_date);
            } else {
                $diff = $diff_1;
            }
            if($diff > 0 && $diff < 7){
                if($reservation_from_date > $from_date){
                    $isDayOfWeekIn = isDayOfWeekIn($reservation_from_date, $diff, $day_of_week, true);
                } else {
                    $isDayOfWeekIn = isDayOfWeekIn($from_date, $diff, $day_of_week, true);
                }
                if($isDayOfWeekIn){
                    return false;
                } else {
                    $hasDaysIn = true;
                }
            }
            elseif($diff <= 0) {
                $hasDaysIn = true;
            } else {
                return false;
            }
        }


        //get diff between pause end date and new reservation end date
        $diff_1 = getDaysDiff($reservation->pausedReservation->to_date, $to_date);
        if($diff_1 > 0){
            //check if end date of reservation is before end date of new reservation
            if($reservation_to_date < $to_date){
                $diff = getDaysDiff($reservation->pausedReservation->to_date, $reservation_to_date);
            } else {
                $diff = $diff_1;
            }
            if($diff >= 0 && $diff < 7){
                if(isDayOfWeekIn($reservation->pausedReservation->to_date, $diff, $day_of_week)){
                    return false;
                } else {
                    $hasDaysIn = true;
                }
            }
            elseif($diff < 0){
                $hasDaysIn = true;
            }
            else {
                return false;
            }
        }
        return $hasDaysIn &&
            isPausedInPlaces($reservation->pausedReservation->pausedReservationPlaces()->get(), $places);
    }

    return $isInsidePausePeriod && isPausedInPlaces($reservation->pausedReservation->pausedReservationPlaces()->get(), $places);
}

function isPausedInPlaces($paused_places, $places){
    foreach($places as $place){
        $floor = $place["floor"];
        $room = $place["room"];
        $pausedPlace = $paused_places->where('floor_id', $floor->id);
        $pausedPlace = $pausedPlace->where('room_id', $room ? $room->id : null);
        $pausedPlace = $pausedPlace->all();
        if(!count($pausedPlace)){
            return false;
        }
    }
    return true;
}

//check if reservation is paused at a specific date
function isPausedOnDay($reservation, $date){
    if(!$reservation->pausedReservation){
        return false;
    }
    $date_1 = createDate($date, true);
    $date_2 = createDate($reservation->pausedReservation->from_date, true);
    $date_3 = createDate($reservation->pausedReservation->to_date, true);
    return $date_1->isBetween($date_2, $date_3, true);
}

function isDayOfWeekIn($date, $days, $day_of_week, $reverse = false){
    $first_dt = createDate($date);
    $second_dt = $first_dt->copy();
    $second_dt->addDays($days);
    if($reverse){
        return ($first_dt->dayOfWeek <= $day_of_week && ($second_dt->dayOfWeek > $day_of_week ||
                    $second_dt->dayOfWeek <= $first_dt->dayOfWeek)) || ($first_dt->dayOfWeek >= $day_of_week &&
                $second_dt->dayOfWeek > $day_of_week && $second_dt->dayOfWeek <= $first_dt->dayOfWeek);
    }
    return ($first_dt->dayOfWeek < $day_of_week && ($second_dt->dayOfWeek >= $day_of_week ||
            $second_dt->dayOfWeek <= $first_dt->dayOfWeek)) || ($first_dt->dayOfWeek > $day_of_week &&
            $second_dt->dayOfWeek >= $day_of_week && $second_dt->dayOfWeek <= $first_dt->dayOfWeek);
}

//returns > 0 if $date_1 is before $date_2
//returns < 0 if $date_1 is after $date_2
function getDaysDiff($date_1, $date_2, $absolute = false){
    $d_1 = createDate($date_1);
    $d_2 = createDate($date_2);
    return $d_1->diffInDays($d_2, $absolute);
}

function compareDay($date, $day_of_week, $before = false){
    $dateObj = createDate($date);
    if($before){
        return $dateObj->dayOfWeek <= $day_of_week;
    }
    return $dateObj->dayOfWeek >= $day_of_week;
}

function getFullStatus($reservation){
    if($reservation->pausedReservation){
        return ['status-paused', __('متوقف'), $reservation->is_approved];
    }
    switch($reservation->is_approved){
        case 0:
            if($reservation->editedReservation){
                return ["status-edited", __("معدل"), 0];
            }
            return ["status-normal", __("الطلب مرسل"), 0];
        case 1:
            if($reservation->hasEditRequest){
                return ["status-sent-edit", __("مرسل للتعديل"), 0];
            }
            if($reservation->deleteRequest) {
                return ['status-sent-delete', __('مرسل للإلغاء'), 0];
            }
            //check if reservation is done
            if(isReservationOver($reservation)) {
                return ['status-ended', __('منتهي'), 1];
            }
            return ["status-success", __("موافق عليه"), 1];
        case -1:
            return ["status-fail", __("مرفوض"), -1];
        case -2:
            return ["status-revoked", __('ملغي'), -2];
    }
}

function isPauseDateAfterNow($pauseDate){
    $now = Carbon::now();
    $now->setTime(0, 0, 0);
    $pd = createDate($pauseDate);
    $pd->setTime(0, 0, 0);
    return $now->lessThanOrEqualTo($pd);
}

/**
 * get places of reservation (long or temp) as collection
 * @param $reservation
 * @return \Illuminate\Support\Collection
 */
function getPlaces($reservation){
    $places = collect([]);
    if($reservation->longReservation){
        $lrds = $reservation->longReservation->longReservationDates()->get();
        $lrds->each(function($date) use (&$places){
            $places = $places->merge($date->longReservationPlaces()->get()->reject(function($value) use ($places){
                $place = $places->where('floor_id', $value->floor_id)->where("room_id", $value->room_id);
                return $place->count() > 0;
            }));
        });
    } elseif($reservation->temporaryReservation){
        $places = $places->merge($reservation->temporaryReservation->temporaryReservationPlaces()->get());
    }
    return $places;
}

/**
 * Check if reservation is finished
 * @param \App\Reservation $reservation
 * @return bool
 */
function isReservationOver($reservation) {
    $today = Carbon::now();
    if($reservation->longReservation) {
        $isOver = $today->greaterThan($reservation->longReservation->to_date);
    } elseif($reservation->temporaryReservation) {
        $dates = $reservation->temporaryReservation->temporaryReservationDates()->get()
            ->where('date', '>=', $today->toDateString())->all();
        $isOver = count($dates) === 0;
    } else {
        $dates = $reservation->manualReservationsDates()->get()
            ->where('date', '>=', $today->toDateString());
        $isOver = count($dates) === 0;
    }
    return $isOver;
}