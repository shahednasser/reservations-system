<?php
/**
 * Created by PhpStorm.
 * User: Shahed
 * Date: 11/12/2019
 * Time: 9:00 PM
 */
namespace App;

use Carbon\Carbon;

class CalendarFunctions {
    public static function addEvent($floor, $room, $currentDate, $from_time, $to_time, $title, $id, $url, $backgroundColor){
        $resourceId = $floor->id;
        if($room){
            $resourceId .= "_".$room->id;
        }
        $start = Carbon::createFromFormat('d-m-Y H:i:s', $currentDate." ".$from_time);
        $end = Carbon::createFromFormat('d-m-Y H:i:s', $currentDate." ".$to_time);
        return ["id" => $id, "title" => $title,
            "start" => $start->toIso8601String(), "end" => $end->toIso8601String(),
            "url" => $url, "backgroundColor" => $backgroundColor,
            "resourceId" => $resourceId, "borderColor" => $backgroundColor,
            "overlap" => false];
    }

    public static function getLocations() {
        //get resources (locations)
        $resources = [];
        $i = 0;
        //get floors
        $allFloors = Floor::with("rooms")->get();
        foreach ($allFloors as $floor){
            $resources[$i] = ["id" => $floor->id, "title" => $floor->name];
            if($floor->number_of_rooms > 0){
                $rooms = $floor->rooms()->get();
                foreach ($rooms as $room){
                    $name = $room->name ?: __('الغرفة ').$room->room_number;
                    $resources[$i]["children"][] = ["id" => $floor->id.'_'.$room->id,
                        "title" => $name];
                }
            }
            $i++;
        }
        return $resources;
    }

    public static function getEvents($currentDate = null, $isWeekly = false) {
        Carbon::setWeekStartsAt(Carbon::MONDAY);
        Carbon::setWeekEndsAt(Carbon::SUNDAY);
        //get today's date and time
        $today = $currentDate ? date_create($currentDate) : Carbon::now();
        if(!$currentDate){
            $currentDate = date_format($today, "d-m-Y");
        }
        $from_datetime = date_format($today, "Y-m-d H:i:s");
        $from_date_arr = explode(" ", $from_datetime);
        $day = intval(date_format($today, "w"));
        //get events
        $events = [];
        //get long reservations
        $longRes = LongReservation::with(["reservation",
            "longReservationDates", "longReservationDates.longReservationPlaces", "longReservationDates.longReservationPlaces"])
            ->where([
                ["from_date", "<=", $from_date_arr[0]],
                ["to_date", ">=", $from_date_arr[0]],
            ])->get();
        foreach($longRes as $res){
            if($res->reservation->is_approved != 1){
                continue;
            }
            $dates = $res->longReservationDates()->get();
            foreach($dates as $date){
                if($date->day_of_week !== $day || isPausedOnDay($res->reservation, $from_date_arr[0]) ||
                    isInsidePausePeriod($res->reservation, $res->from_date,
                        $res->to_date, $from_date_arr[0], $from_date_arr[0], true, $date->day_of_week)){
                    continue;
                }
                foreach($date->longReservationPlaces()->get() as $place){
                    $floor = $place->floor()->withTrashed()->first();
                    $room = $place->room()->withTrashed()->first();
                    $title = $res->reservation->event_name." - ".$res->reservation->committee;
                    if($isWeekly) {
                        $title .= "\n من ".
                            format_date($res->from_date)." إلى ".format_date($res->to_date);
                    }
                    $events[] = self::addEvent($floor, $room, $currentDate, $date->from_time, $date->to_time,
                        $title,
                        "long_".$res->reservation->id, '/show-reservation/'.$res->reservation->id,
                        '#00652E');
                }
            }
        }
        //get temporary Reservations
        $tempRes = TemporaryReservation::with(["temporaryReservationDates", "reservation",
            "temporaryReservationPlaces.floor", "temporaryReservationPlaces.room"])->get();
        foreach ($tempRes as $res) {
            if($res->reservation->is_approved != 1){
                continue;
            }
            $ds = $res->temporaryReservationDates()->get();
            $placesCol = $res->temporaryReservationPlaces()->get();
            $places = [];
            foreach($placesCol as $place){
                $floor = $place->floor()->withTrashed()->first();
                $room = $place->room()->withTrashed()->first();
                $places[] = ["floor" => $floor, "room" => $room];
            }
            foreach($ds as $date){
                if($date->date == $from_date_arr[0] && !isPausedOnDay($res->reservation, $from_date_arr[0])){
                    foreach ($places as $place){
                        $floor = $place["floor"];
                        $room = $place["room"];
                        $events[] = self::addEvent($floor, $room, $currentDate, $date->from_time, $date->to_time,
                            $res->reservation->event_name." - ".$res->reservation->committee,
                            "temporary_".$res->reservation->id, '/show-reservation/'.$res->reservation->id,
                            '#94111E');
                    }
                }
            }
        }
        $manualReservations = ManualReservation::with(['manualPlace', 'manualReservationsDates'])
            ->get()
            ->reject(function($value) use ($from_date_arr){
                $dates = $value->manualReservationsDates()->get();
                $noDates = true;
                foreach($dates as $date){
                    if($date->date == $from_date_arr[0] && !isPausedOnDay($value, $from_date_arr[0])){
                        $noDates = false;
                        break;
                    }
                }
                return $noDates;
            });

        foreach($manualReservations as $manualReservation){
            $floor = $manualReservation->manualPlace->floor;
            foreach($manualReservation->manualReservationsDates()->get() as $mrd){
                if($mrd->date == $from_date_arr[0] && !isPausedOnDay($manualReservation, $from_date_arr[0])){
                    $events[] = self::addEvent($floor, null, $currentDate, $mrd->from_time, $mrd->to_time,
                        $manualReservation->full_name ? $manualReservation->event_type." - ".$manualReservation->full_name :
                            $manualReservation->event_type,
                        "manual_".$manualReservation->id, '/view-admin-reservation/'.$manualReservation->id,
                        '#004071');
                }
            }
        }
        return $events;
    }

}