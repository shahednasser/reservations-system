<?php

namespace App\Http\Controllers;

use App\Floor;
use App\ManualPlace;
use App\ManualReservation;
use App\PausedReservation;
use App\PausedReservationPlace;
use App\Reservation;
use App\Room;
use Illuminate\Http\Request;
use Auth;
use Validator;
use Notification;
use App\Notifications\UserNotifications;

class MassEditingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware('admin')->except('pauseReservation', 'editPausedReservation', 'deletePauseReservation');
    }

    public function showList(){
        $reservations = Reservation::orderBy('date_created', "desc")->get();
        $manualReservations = ManualReservation::orderBy('date_created', 'desc')->get();
        $allReservations = $reservations->concat($manualReservations)->sortByDesc('date_created');
        //in case we need to add pagination later
        //$allReservations = paginate($allReservations, $this->pagination_nb)->setPath($request->path());
        $user = Auth::user();
        $floors = Floor::all();
        $rooms = Room::all();

        return view('mass-editing-list', ["user" => $user, "reservations" => $allReservations,
                                                "floors" => $floors, "rooms" => $rooms]);
    }

    public function massReject(Request $request){
        $reservations = [];
        foreach($request->all() as $key => $value){
            if(strpos($key, 'long') !== false ||
                strpos($key, 'temp') !== false){
                $keyArr = explode('_', $key);
                $reservation = null;
                if(count($keyArr) === 2){
                    switch ($keyArr[0]){
                        case 'long':
                        case 'temp':
                            $reservation = Reservation::find($keyArr[1]);
                            break;
                    }
                    if(!$reservation){
                        return back()->withErrors([$key => __('الحجز غير موجود')])->withInput();
                    }
                    $reservations[] = $reservation;
                }
            }
        }

        $reservations = collect($reservations);
        $user = Auth::user();
        $reservations->each(function ($value) use($user){
            $value->is_approved = -2;
            $value->save();
            if(get_class($value) !== "App\ManualReservation" &&
                !$value->user()->withTrashed()->first()->trashed() && $value->user->id != $user->id){
                $url = url('/show-reservation/'.$value->id);
                if($value->user->isAdmin()){
                    $url = url('/view-reservation/'.$value->id);
                }
                Notification::send($value->user, new UserNotifications("تم إلغاء الحجز ".$value->event_name,
                    $url));
            }
        });

        $request->session()->flash('message', __("تم إلغاء الحجوزات"));
        $request->session()->flash("message_class", "success");
        return back();
    }

    public function massDelete(Request $request){
        $reservations = [];
        foreach($request->all() as $key => $value){
            if(strpos($key, 'manual') !== false ||
                strpos($key, 'long') !== false ||
                strpos($key, 'temp') !== false){
                $keyArr = explode('_', $key);
                $reservation = null;
                if(count($keyArr) === 2){
                    switch ($keyArr[0]){
                        case 'manual':
                            $reservation = ManualReservation::find($keyArr[1]);
                            break;
                        case 'long':
                        case 'temp':
                            $reservation = Reservation::find($keyArr[1]);
                            break;
                    }
                    if(!$reservation){
                        return back()->withErrors([$key => __('الحجز غير موجود')])->withInput();
                    }
                    $reservations[] = $reservation;
                }
            }
        }

        $reservations = collect($reservations);
        $user = Auth::user();
        $reservations->each(function ($reservation) use($user){
            if($reservation->is_approved == 1){
                $url = url('/my-reservation/'.$reservation->id);
                $cond = get_class($reservation) !== "App\ManualReservation" &&
                    !$reservation->user()->withTrashed()->first()->trashed();
                $id = $reservation->id;
                $event_name = $reservation->event_name;
                $reservation_user = $reservation->user;
                $reservation->delete();
                if($cond){
                    if($reservation->user->isAdmin()){
                        $url = url('/new-reservations/'.$id);
                    }
                    Notification::send($reservation_user,
                        new UserNotifications(__('تم إلغاء الطلب ').$event_name,
                        $url));
                }
            }
            else{
                $reservation->delete();
            }
        });

        $request->session()->flash('message', __("تم حذف الحجوزات"));
        $request->session()->flash("message_class", "success");
        return back();
    }

    public function massPause(Request $request){
        $reservations = [];
        //validate date
        Validator::make($request->all(), [
            "pause_from_date" => 'required|date|before_or_equal:pause_to_date',
            "pause_to_date" => 'required|date',
            "pausedReservationPlaces" => "required|min:1"
        ])->validate();
        $from_date = $request->pause_from_date;
        $to_date = $request->pause_to_date;
        foreach($request->all() as $key => $value){
            if(strpos($key, 'manual') !== false ||
                strpos($key, 'long') !== false ||
                strpos($key, 'temp') !== false){
                $keyArr = explode('_', $key);
                $reservation = null;
                if(count($keyArr) === 2){
                    switch ($keyArr[0]){
                        case 'manual':
                            $reservation = ManualReservation::find($keyArr[1]);
                            break;
                        case 'long':
                        case 'temp':
                            $reservation = Reservation::find($keyArr[1]);
                            break;
                    }
                    if(!$reservation){
                        return back()->withErrors([$key => __('الحجز غير موجود')])->withInput();
                    }
                    if($reservation->is_approved === 1){
                        $reservations[] = $reservation;
                    }
                }
            }
        }

        $reservations = collect($reservations);
        $user = Auth::user();
        $reservations->each(function ($value) use($from_date, $to_date, $user, $request){
            $paused_reservation = $value->pausedReservation;
            $isReservation = get_class($value) == "App\Reservation";
            if($paused_reservation){
                //check if conflicting reservations
                $hasConflicting = false;
                if(!($paused_reservation->from_date >= $from_date && $paused_reservation->to_date <= $to_date)) {
                    $conflicting = $this->getConflictingReservations($isReservation ? "reservation" : "manual",
                        $value, $paused_reservation);
                    $hasConflicting = $conflicting->count() !== 0;
                }
                if(!$hasConflicting){
                    $paused_reservation->from_date = $from_date;
                    $paused_reservation->to_date = $to_date;
                    $paused_reservation->save();
                    if($isReservation){
                        $paused_reservation->pausedReservationPlaces()->delete();
                        $places = $this->validateAndGetPlaces($request, $value);
                        if($places !== false){
                            $this->savePlaces($places, $paused_reservation);
                        }
                    }
                }
            } else {
                $paused_reservation = new PausedReservation([
                    "from_date" => $from_date,
                    "to_date" => $to_date
                ]);
                if($isReservation){
                    $paused_reservation->reservation()->associate($value);
                } else {
                    $paused_reservation->manualReservation()->associate($value);
                }
                $paused_reservation->save();
                $places = $this->validateAndGetPlaces($request, $value);
                if($places !== false){
                    $this->savePlaces($places, $paused_reservation);
                }
            }
            if($isReservation && !$value->user()->withTrashed()->first()->trashed() && $value->user->id !== $user->id){
                if(get_class($value) === "App\Reservation"){
                    $url = url('/show-reservation/'.$value->id);
                    if($value->user->isAdmin()){
                        $url = url('/view-reservation/'.$value->id);
                    }
                    $event_name = $value->event_name;
                } else {
                    $url = url('/view-admin-reservation/'.$value->id);
                    $event_name = $value->event_type;
                }
                Notification::send($value->user, new UserNotifications("تم إيقاف الحجز  ".$event_name." لوقت مؤقت",
                $url));
            }
        });

        $request->session()->flash('message', __("تم إيقاف الحجوزات لوقت مؤقت"));
        $request->session()->flash("message_class", "success");
        return back();
    }

    /**
     * Pause a reservation (for admin and creator user only)
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function pauseReservation(Request $request){
        $user = Auth::user();
        $reservation_id = $request->reservation_id;
        if(!$reservation_id){
            $request->session()->flash("message", __('الحجز غير موجود'));
            $request->session()->flash("message_class", "danger");
            return back();
        }
        //check type based on referer
        $referer = request()->headers->get('referer');
        $reservation = null;
        $type = "";
        if(strpos($referer, "view-admin-reservation") !== FALSE){
            $reservation = ManualReservation::find($reservation_id);
            $type = "manual";
        } elseif(strpos($referer, "view-reservation") !== FALSE || strpos($referer, "show-reservation")) {
            $reservation = Reservation::find($reservation_id);
            $type = "reservation";
        }
        if(!$reservation){
            $request->session()->flash("message", __('الحجز غير موجود'));
            $request->session()->flash("message_class", "danger");
            return back();
        }

        if(!$user->isAdmin() && (($type === "reservation" && $reservation->user->id !== $user->id)
                || $type === "manual")){
            redirect('/');
        }

        if($reservation->pausedReservation){
            $request->session()->flash("message", __('الحجز متوقف'));
            $request->session()->flash("message_class", "danger");
            return back();
        }
        if($reservation->longReservation || $reservation->temporaryReservation){
            //validate from and to dates
            Validator::make($request->all(), [
                "from_date" => "required|date",
                "to_date" => "required|date|after_or_equal:from_date",
                "pausedReservationPlaces" => "required|min:1"
            ])->validate();
            //validate places and put them in array
            $places = $this->validateAndGetPlaces($request, $reservation);
            if($places === false){
                return back();
            }
        } else {
            $places = [["floor" => Floor::find("1"), "room" => null]];
        }

        //validate paused from date is after now
        if(!isPauseDateAfterNow($request->from_date)){
            $request->session()->flash("message", __('يجب أن يكون وقت إيقاف الحجز يساوي أو بعد تاريخ اليوم'));
            $request->session()->flash("message_class", "danger");
            return back();
        }

        //all valid, add paused reservation
        $pausedReservation = new PausedReservation([
            "from_date" => $request->from_date,
            "to_date" => $request->to_date
        ]);
        if($type === "manual"){
            $pausedReservation->manualReservation()->associate($reservation);
        } else {
            $pausedReservation->reservation()->associate($reservation);
        }

        $pausedReservation->save();

        //save paused reservation places
        $this->savePlaces($places, $pausedReservation);

        $request->session()->flash("message", __('تم إيقاف الحجز لوقت مؤقت'));
        $request->session()->flash("message_class", "success");
        if($type !== "manual" && !$reservation->user()->withTrashed()->first()->trashed() && $reservation->user->id !== $user->id){
            if(get_class($reservation) === "App\Reservation"){
                $url = url('/show-reservation/'.$reservation->id);
                if($reservation->user->isAdmin()){
                    $url = url('/view-reservation/'.$reservation->id);
                }
                $event_name = $reservation->event_name;
            } else {
                $url = url('/view-admin-reservation/'.$reservation->id);
                $event_name = $reservation->event_type;
            }
            Notification::send($reservation->user, new UserNotifications("تم إيقاف الحجز  ".$event_name." لوقت مؤقت",
            $url));
        }

      return back();
    }


    /** Edit a Paused Reservation's Period (for admin and creator user)
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function editPausedReservation(Request $request){
        $user = Auth::user();
        $reservation_id = $request->reservation_id;
        if(!$reservation_id){
            $request->session()->flash("message", __('الحجز غير موجود'));
            $request->session()->flash("message_class", "danger");
            return back();
        }
        //check type based on referer
        $referer = request()->headers->get('referer');
        $reservation = null;
        $type = "";
        if(strpos($referer, "view-admin-reservation") !== FALSE){
            $reservation = ManualReservation::find($reservation_id);
            $type = "manual";
        } elseif(strpos($referer, "view-reservation") !== FALSE || strpos($referer, "show-reservation")) {
            $reservation = Reservation::find($reservation_id);
            $type = "reservation";
        }
        if(!$reservation){
            $request->session()->flash("message", __('الحجز غير موجود'));
            $request->session()->flash("message_class", "danger");
            return back();
        }

        if(!$user->isAdmin() && $reservation->user->id !== $user->id){
            redirect('/');
        }
        
        $pausedReservation = $reservation->pausedReservation;
        if(!$pausedReservation){
            $request->session()->flash("message", __('الحجز غير متوقف'));
            $request->session()->flash("message_class", "danger");
            return back();
        }

        if($reservation->longReservation || $reservation->temporaryReservation){
            //validate from and to dates
            Validator::make($request->all(), [
                "from_date" => "required|date",
                "to_date" => "required|date|after_or_equal:from_date",
                "pausedReservationPlaces" => "required|min:1"
            ])->validate();
            //validate places and put them in array
            $places = $this->validateAndGetPlaces($request, $reservation);
            if($places === false){
                return back();
            }
        } else {
            $places = [["floor" => Floor::find("1"), "room" => null]];
        }

        //validate paused from date is after now
        if(!isPauseDateAfterNow($request->from_date)){
            $request->session()->flash("message", __('يجب أن يكون وقت إيقاف الحجز يساوي أو بعد تاريخ اليوم'));
            $request->session()->flash("message_class", "danger");
            return back();
        }

        if(!($pausedReservation->from_date >= $request->from_date && $pausedReservation->to_date <= $request->to_date)){
            //check if there are conflicting reservations
            $conflicting = $this->getConflictingReservations($type, $reservation, $pausedReservation);

            if($conflicting->count()){
                $request->session()->flash("message", __('هناك حجوزات موجودة خلال وقت إيقاف الحجز'));
                $request->session()->flash("message_class", "danger");
                return back();
            }
        }

        //edit paused reservation
        $pausedReservation->from_date = $request->from_date;
        $pausedReservation->to_date = $request->to_date;
        $pausedReservation->save();

        //edit pausedReservationPlaces
        //delete previous pausedReservationPlaces and add new ones
        $pausedReservation->pausedReservationPlaces()->delete();
        //save paused reservation places
        $this->savePlaces($places, $pausedReservation);


        $request->session()->flash("message", __('تم تعديل إيقاف الحجز'));
        $request->session()->flash("message_class", "success");
        if(get_class($reservation) !== "App\ManualReservation" &&
            !$reservation->user()->withTrashed()->first()->trashed() && $reservation->user->id !== $user->id){
            if(get_class($reservation) === "App\Reservation"){
                $url = url('/show-reservation/'.$reservation->id);
                if($reservation->user->isAdmin()){
                    $url = url('/view-reservation/'.$reservation->id);
                }
                $event_name = $reservation->event_name;
            } else {
                $url = url('/view-admin-reservation/'.$reservation->id);
                $event_name = $reservation->event_type;
            }
            Notification::send($reservation->user, new UserNotifications("تم تعديل إيقاف الحجز  ".$event_name." لوقت مؤقت",
            $url));
        }

        return back();
    }

    /** Delete pause reservation (for admin and creator user)
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deletePauseReservation(Request $request){
        $user = Auth::user();
        $reservation_id = $request->reservation_id;
        if(!$reservation_id){
            $request->session()->flash("message", __('الحجز غير موجود'));
            $request->session()->flash("message_class", "danger");
            return back();
        }
        //check type based on referer
        $referer = request()->headers->get('referer');
        $reservation = null;
        $type = "";
        if(strpos($referer, "view-admin-reservation") !== FALSE){
            $reservation = ManualReservation::find($reservation_id);
            $type = "manual";
        } elseif(strpos($referer, "view-reservation") !== FALSE || strpos($referer, "show-reservation")) {
            $reservation = Reservation::find($reservation_id);
            $type = "reservation";
        }
        if(!$reservation){
            $request->session()->flash("message", __('الحجز غير موجود'));
            $request->session()->flash("message_class", "danger");
            return back();
        }

        if(!$user->isAdmin() && $reservation->user->id !== $user->id){
            redirect('/');
        }
        
        $pausedReservation = $reservation->pausedReservation;
        if(!$pausedReservation){
            $request->session()->flash("message", __('الحجز غير متوقف'));
            $request->session()->flash("message_class", "danger");
            return back();
        }

        //check if there are conflicting reservations
        $conflicting = $this->getConflictingReservations($type, $reservation, $pausedReservation);

        if($conflicting->count()){
            $request->session()->flash("message", __('هناك حجوزات موجودة خلال وقت إيقاف الحجز'));
            $request->session()->flash("message_class", "danger");
            return back();
        }
        $pausedReservation->delete();

        $request->session()->flash("message", __('تم إلغاء إيقاف الحجز'));
        $request->session()->flash("message_class", "success");
        if($type !== "manual" &&
            !$reservation->user()->withTrashed()->first()->trashed() && $reservation->user->id !== $user->id){
            if(get_class($reservation) === "App\Reservation"){
                $url = url('/show-reservation/'.$reservation->id);
                if($reservation->user->isAdmin()){
                    $url = url('/view-reservation/'.$reservation->id);
                }
                $event_name = $reservation->event_name;
            } else {
                $url = url('/view-admin-reservation/'.$reservation->id);
                $event_name = $reservation->event_type;
            }
            Notification::send($reservation->user, new UserNotifications("تم إلغاء إيقاف الحجز  ".$event_name." لوقت مؤقت",
            $url));
        }
        return back();
    }
    
    public function massDeletePausedReservations(Request $request){
        $pausedReservations = [];
        foreach($request->all() as $key => $value){
            if(strpos($key, 'manual') !== false ||
                strpos($key, 'long') !== false ||
                strpos($key, 'temp') !== false){
                $keyArr = explode('_', $key);
                $reservation = null;
                if(count($keyArr) === 2){
                    switch ($keyArr[0]){
                        case 'manual':
                            $reservation = ManualReservation::find($keyArr[1]);
                            break;
                        case 'long':
                        case 'temp':
                            $reservation = Reservation::find($keyArr[1]);
                            break;
                    }
                    if(!$reservation){
                        return back()->withErrors([$key => __('الحجز غير موجود')])->withInput();
                    }
                    $pausedReservation = $reservation->pausedReservation;
                    if($reservation->is_approved === 1 && $pausedReservation){
                        $pausedReservations[] = $pausedReservation;
                    }
                }
            }
        }

        $pausedReservations = collect($pausedReservations);
        $user = Auth::user();
        $pausedReservations->each(function($value)use ($user){
            $reservation = $value->reservation;
            if(!$reservation){
                $reservation = $value->manualReservation;
            }
            if(get_class($reservation) === "App\ManualReservation"){
                $type = "manual";
            } else {
                $type = "reservation";
            }
            $conflicting = $this->getConflictingReservations($type, $reservation, $value);
            dd($type, $conflicting);
            if(!$conflicting->count()){
                $value->delete();
                if(get_class($reservation) !== "App\ManualReservation" &&
                    !$reservation->user()->withTrashed()->first()->trashed() && $reservation->user->id !== $user->id){
                    if(get_class($reservation) === "App\Reservation"){
                        $url = url('/show-reservation/'.$reservation->id);
                        if($reservation->user->isAdmin()){
                            $url = url('/view-reservation/'.$reservation->id);
                        }
                        $event_name = $reservation->event_name;
                    } else {
                        $url = url('/view-admin-reservation/'.$reservation->id);
                        $event_name = $reservation->event_type;
                    }
                    Notification::send($reservation->user, new UserNotifications("تم إيقاف الحجز  ".$event_name." لوقت مؤقت",
                        $url));
                }
            }
        });
        
        $request->session()->flash('message', __("تم إلغاء إيقاف الحجوزات لوقت مؤقت"));
        $request->session()->flash("message_class", "success");
        return back();
    }

    private function validateAndGetPlaces($request, $reservation){
        $places = [];
        foreach($request->input('pausedReservationPlaces') as $pausedReservationPlace){
            $arr = explode("_", $pausedReservationPlace);
            if(count($arr) < 1 || count($arr) > 2){
                $request->session()->flash("message", __('يجب إختيار أماكن صحيحة'));
                $request->session()->flash("message_class", "danger");
                return false;
            }

            //if length of array is one, then it has just floor. else it also has room
            $floor = Floor::withTrashed()->find($arr[0]);
            if(!$floor || $floor->trashed()){
                $request->session()->flash("message", __('المكان غير موجود'));
                $request->session()->flash("message_class", "danger");
                return false;
            }

            $room = null;
            if(count($arr) === 2){
                $room = Room::withTrashed()->find($arr[1]);
                if(!$room || $room->trashed() || $room->floor_id !== $floor->id){
                    $request->session()->flash("message", __('المكان غير موجود'));
                    $request->session()->flash("message_class", "danger");
                    return false;
                }
            } else {
                if($floor->number_of_rooms){
                    $request->session()->flash("message", __('المكان غير موجود'));
                    $request->session()->flash("message_class", "danger");
                    return false;
                }
            }

            if($reservation->longReservation){
                //validate if place is in longReservationPlaces
                $lrds = $reservation->longReservation->longReservationDates()->get();
                $hasPlace = $lrds->reject(function($date) use ($floor, $room){
                    $places = $date->longReservationPlaces()->get()->where('floor_id', $floor->id);
                    $places = $places->where('room_id', $room ? $room->id : null);
                    $places = $places->all();
                    return count($places) === 0;
                });
                if(!$hasPlace->count()){
                    continue;
                }
            } elseif($reservation->temporaryReservation) {
                //validate if place in temporaryReservationPlaces
                $trps = $reservation->temporaryReservation->temporaryReservationPlaces()->get()->where('floor_id', $floor->id);
                $trps = $trps->where('room_id', $room ? $room->id : null);
                $trps = $trps->all();
                if(!count($trps)){
                    continue;
                }
            } else {
                //TODO validate based on manualReservationPlaces
                if($floor->id !== 1){
                    continue;
                }
            }

            $places[] = ["floor" => $floor, "room" => $room];
        }
        return $places;
    }

    private function savePlaces($places, $pausedReservation){
        foreach($places as $place){
            $pausedReservationPlace = new PausedReservationPlace();
            $pausedReservationPlace->pausedReservation()->associate($pausedReservation);
            $pausedReservationPlace->floor()->associate($place["floor"]);
            if($place["room"]){
                $pausedReservationPlace->room()->associate($place["room"]);
            }
            $pausedReservationPlace->save();
        }
    }

    private function getConflictingReservations($type, $reservation, $pausedReservation){
        $conflicting = collect([]);
        if($type === "reservation"){
            if($reservation->longReservation){
                if(dateBetween($reservation->longReservation->from_date, $pausedReservation->from_date,
                    $reservation->longReservation->to_date, $pausedReservation->to_date)){
                    //get date difference between from date and to date of paused
                    $to_date = createDate($pausedReservation->to_date);
                    $from_date = createDate($pausedReservation->from_date);
                    $diff_days = $to_date->diffInDays($from_date);
                    //get long reservation dates
                    $conflicting = $reservation->longReservation->longReservationDates()
                        ->get()->reject(function($value) use ($diff_days, $pausedReservation, $from_date){
                            if(!isDayOfWeekIn($from_date->subDay(1)->toDateString(), $diff_days, $value->day_of_week)){
                                return true;
                            }

                            //get places
                            $placesCol = $value->longReservationPlaces()->get();
                            $places = [];
                            foreach ($placesCol as $place){
                                $floor = $place->floor()->withTrashed()->first();
                                $room = $place->room()->withTrashed()->first();
                                $places[] = ["floor" => $floor, "room" => $room];
                            }

                            //check if there are conflicting reservations
                            $other_res = [];
                            getOtherReservationsForLong($other_res, 0, $pausedReservation->from_date,
                                $pausedReservation->to_date, $value->longReservation, $value->day_of_week, $places,
                                $value->from_time, $value->to_time);
                            return count($other_res) === 0;
                        });
                }
            } else {
                //get places
                $placesCol = $reservation->temporaryReservation->temporaryReservationPlaces()->get();
                $places = [];
                foreach($placesCol as $place){
                    $floor = $place->floor()->withTrashed()->first();
                    $room = $place->room()->withTrashed()->first();
                    $places[] = ["floor" => $floor, "room" => $room];
                }
                $conflicting = $reservation->temporaryReservation->temporaryReservationDates()
                    ->get()->reject(function($value) use ($pausedReservation, $places){
                        if(!dateBetween($pausedReservation->from_date, $value->date,
                            $pausedReservation->to_date, $value->date)){
                            return true;
                        }
                        $other_res = [];
                        getOtherReservationsForTemp($other_res, 0, $value->date,
                            $value->temporaryReservation, $places, $value->from_time,
                            $value->to_time);
                        return count($other_res) === 0;
                    });
            }
        } else {
            $placesCol = ManualPlace::all();
            $places = [];
            foreach($placesCol as $place){
                $floor = $place->floor()->withTrashed()->first();
                $room = $place->room()->withTrashed()->first();
                $places = ["floor" => $floor, "room" => $room];
            }
            $conflicting = $reservation->manualReservationsDates()
                ->get()->reject(function($value) use ($pausedReservation, $places){
                    if(!dateBetween($pausedReservation->from_date, $value->date,
                        $pausedReservation->to_date, $value->date)){
                        return true;
                    }
                    $other_res = [];
                    getOtherReservationsForManual($other_res, 0, $value->date,
                        $value->manualReservation->id, $places, $value->from_time, $value->to_time);
                    return count($other_res) === 0;
                });
        }
        return $conflicting;
    }
}
