<?php

namespace App\Http\Controllers;

use App\PreviousCalendar;
use Illuminate\Http\Request;

use App\Reservation;
use App\TemporaryReservation;
use App\LongReservation;
use Carbon\Carbon;
use App\Floor;
use App\ManualReservation;
use Auth;
use App\User;
use Validator;
use App\CalendarFunctions;

class CalendarController extends Controller
{

    public function __construct(){
      $this->middleware('auth');
    }

    public function showCalendar($date = null){
      $user = Auth::user();
      Carbon::setWeekStartsAt(Carbon::MONDAY);
      Carbon::setWeekEndsAt(Carbon::SUNDAY);
      //get today's date and time
      $today = $date ? date_create($date) : Carbon::now();
      return view('calendar', ['user' => $user, "date" => $today]);
    }

    public function getReservationsCalendar($currentDate = null){
        $validator = Validator::make(["date" => $currentDate], [
            "date" => "nullable|date_format:d-m-Y"
        ]);
        if($validator->fails()){
            return ["error" => __('التاريخ غير صحيح')];
        }
        if($currentDate) {
            $today = Carbon::now();
            if($today->isAfter($currentDate)) {
                $previousCalendar = PreviousCalendar::where('date', format_db_date($currentDate))
                    ->where('is_weekly', 0)->first();
                if($previousCalendar) {
                    return json_decode($previousCalendar->data, true);
                }
            }
        }
        //get resources (locations)
        //$resources = $this->getLocations();
        $resources = CalendarFunctions::getLocations();
        //get events
        //$events = $this->getEvents($currentDate);
        $events = CalendarFunctions::getEvents($currentDate);

        return ["resources" => $resources, "events" => $events];
    }

    public function showWeeklyCalendar() {
        $user = Auth::user();
        return view('weekly-calendar', ["user" => $user]);
    }

    public function getWeekReservations($startDate) {
        $startDate = Carbon::createFromFormat('d-m-Y', $startDate);
        $reservations = [];
        for($i = 0; $i < 7; $i++) {
            $currentDate = $startDate->format('d-m-Y');
            $previousCalendar = PreviousCalendar::where('date', format_db_date($currentDate))
                ->where('is_weekly', 1)->first();
            if($previousCalendar) {
                $reservations = array_merge($reservations,
                    json_decode($previousCalendar->data, true));
                $startDate->addDay(1);
                continue;
            }
            $reservations = array_merge($reservations,
                CalendarFunctions::getEvents($currentDate, true));
            $startDate->addDay(1);
        }
        return response()->json(["events" => $reservations]);
    }

    public function getCalendarResources() {
        return response()->json(["resources" => CalendarFunctions::getLocations()]);
    }
}