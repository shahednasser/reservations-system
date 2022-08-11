<?php

namespace App\Http\Controllers;

use App\LongReservationPlace;
use App\User;
use Illuminate\Http\Request;
use Auth;
use Validator;
use App\Reservation;
use App\LongReservation;
use App\TemporaryReservation;
use App\LongReservationDate;
use App\TemporaryReservationDate;
use App\TemporaryReservationPlace;
use App\Floor;
use App\Room;
use App\EditedReservation;
use App\DeleteRequest;
use App\EditRequest;
use App\ReservationsRejection;
use App\ManualReservation;
use App\PausedReservation;
use Notification;
use App\Notifications\UserNotifications;

class ReservationsController extends Controller
{
  private $pagination_nb = 20;

    public function __construct(){
      $this->middleware('auth');
      $this->middleware('admin')->except(["getUserReservations", "viewReservation", "deleteReservation", "approveEdit",
                                          "editReservation", "postEditReservation", "addReservation", "postAddReservation",
                                          "checkNewReservation", "checkReservations"]);
      $this->middleware('notadmin')->only(["getUserReservations", "approveEdit",
                                          "editReservation", "postEditReservation"]);
    }

    /**
     * Show a list of new reservation requests
     * @param Request $request
     * @return mixed
     */
    public function showNewReservations(Request $request){
      $reservations = Reservation::where("is_approved", 0)->orderBy('date_created', 'desc')->get()
                                                          ->load(['user', 'longReservation', 'temporaryReservation'])
                                                          ->reject(function($value){
                                                            return $value->editedReservation != null;
                                                          });
      $delete_reservations = DeleteRequest::all();
      foreach ($delete_reservations as $dr) {
        $reservations = $reservations->concat([$dr->reservation]);
      }
      $reservations = paginate($reservations, $this->pagination_nb)->setPath($request->path());
      $user = Auth::user();
      return view('new-reservations')->withReservations($reservations)->withUser($user)->withStatus('new');
    }

    /**
     * Show a reservation request
     * @param $id
     * @return mixed
     */
    public function showReservation($id){
      $reservation = Reservation::find($id);
      if(!$reservation || ($reservation->is_approved != 0 && !$reservation->deleteRequest)){
        abort(404);
      }
      $error = null;
      $reservation->load(["longReservation", "longReservation.longReservationDates",
                          "longReservation.longReservationDates.longReservationPlaces.floor",
                          "longReservation.longReservationDates.longReservationPlaces.room",
                          "temporaryReservation",
                          "temporaryReservation.temporaryReservationDates",
                          "temporaryReservation.temporaryReservationPlaces",
                          "user", "hasEditRequest.reservation", "isEditRequest.reservation"]);
      //get other events at the same time
      $other_reservations = $this->getOtherReservations($reservation);
      $user = Auth::user();
      $floors = Floor::all();
      $rooms = Room::all();
      //dd($other_reservations);
      return view("reservation")->withReservation($reservation)->withUser($user)
                                ->with("other_reservations", $other_reservations)
                                ->withFloors($floors)->withRooms($rooms)
                                ->withError($error);
    }

    /**
     * ajax request to check reservations conflicting with a reservation request
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function checkReservations(Request $request, $id){
      if(!Auth::user()){
        return response("Unauthenticated", 401);
      }
      $fields = $request->fields;
      $from_date = $request->from_date;
      $to_date = $request->to_date;
      if(!$fields){
        return response()->json(["error" => "المعلومات خاطئة."]);
      }
      $reservation = Reservation::find($id);
      if(!$reservation){
        return response()->json(["error" => "الحجز غير موجود"]);
      }
      if($reservation->longReservation){
        if(!$from_date || !$to_date){
          return response()->json(["error" => "المعلومات خاطئة."]);
        }
        if($from_date >= $to_date){
          return response()->json(["error" => "لا يمكن تاريخ البداية بعد تاريخ النهاية"]);
        }
      }

      $reservation->load(["longReservation", "longReservation.longReservationDates",
                          "longReservation.longReservationDates.longReservationPlaces.floor",
                            "longReservation.longReservationDates.longReservationPlaces.room",
                          "temporaryReservation",
                          "temporaryReservation.temporaryReservationDates",
                          "temporaryReservation.temporaryReservationPlaces",
                          "user"]);
      //get reservations conflicting with reservation request
      $other_reservations = $this->getOtherReservationsWithFields($reservation, $fields, $from_date, $to_date);
      if(isset($other_reservations["error"])){
        return response()->json($other_reservations);
      }
      return response()->json(["reservations" => $other_reservations]);
    }

    /**
     * post request - edit a reservation (for admin)
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendEditReservation(Request $request, $id){
      $reservation = Reservation::find($id);
      if(!$reservation){
        abort(404);
      }
      $fields = $request->fields;
      $from_date = $request->from_date;
      $to_date = $request->to_date;

      if(!$fields){
        return response()->json(["error" => "المعلومات خاطئة."]);
      }

      $reservation->load(["longReservation", "longReservation.longReservationDates",
                          "temporaryReservation",
                          "temporaryReservation.temporaryReservationDates",
                          "temporaryReservation.temporaryReservationPlaces",
                          "user", "isEditRequest", "isEditRequest.reservation"]);
      if($reservation->isEditRequest){
        $old_res = $reservation->isEditRequest->reservation;
        $old_res->committee = $reservation->committee;
        $old_res->event_name = $reservation->event_name;
        $old_res->supervisors = $reservation->supervisors;
        $old_res->notes = $reservation->notes;
        $reservation->delete();
        $reservation = $old_res;
      }
      if($reservation->longReservation){
        if(!$from_date || !$to_date){
          return response()->json(["error" => "يجب إختيار تاريخ البداية والنهاية"]);
        }
        if($from_date >= $to_date){
          return response()->json(["error" => "لا يمكن لتاريخ البداية أن يكون في نفس وقت أو بعد تاريخ النهاية."]);
        }
      }

      $other_reservations = $this->getOtherReservationsWithFields($reservation, $fields, $from_date, $to_date);
      if(isset($other_reservations["error"])){
        return response()->json($other_reservations);
      }
      elseif(count($other_reservations) > 0){
        foreach($other_reservations as $or){
          if(count($or["reservations"]) > 0){
            return response()->json(["error" => "هناك نشاطات اخرى في نفس الوقت أو نفس المكان. قم بتعديل المعلومات وحاول مرة أخرى."]);
          }
        }
      }

      if($reservation->longReservation){
        $long = $reservation->longReservation;
        $to_save = [];
        $to_delete = $long->longReservationDates()->get();

        if(count($fields)){
          for($i = 0; $i < 7; $i++){
            if(isset($fields["day_$i"])){
              $from_time = $fields["from_time_$i"];
              $to_time = $fields["to_time_$i"];
              if(!validateTime($from_time) || !validateTime($to_time)){
                return response()->json(["error" => "الوقت غير صالح."]);
              }
              if($from_time >= $to_time){
                return response()->json(["error" => "لا يمكن أن يكون وقت البداية بعد أو في نفس وقت النهاية للنشاط."]);
              }
              $event = $fields["event_$i"];
                $day = $i == 6 ? 0 : $i + 1;
                $places = [];
                foreach($fields["place_$i"] as $place){
                    $placeArr = explode("_", $place);
                    $floor = null;
                    $room = null;
                    if(count($placeArr) > 1){
                        $floor = Floor::find($placeArr[0]);
                        if(!$floor || $floor->trashed()){
                            return response(["error" => "بعض المعلومات خاطئة. قم بتعديلها وأعد المحاولة."]);
                        }
                        $room = Room::find($placeArr[1]);
                        if(!$room || $room->trashed()){
                            return response(["error" => "بعض المعلومات خاطئة. قم بتعديلها وأعد المحاولة."]);
                        }
                    }
                    elseif(count($placeArr) == 1){
                        $floor = Floor::find($placeArr[0]);
                        if(!$floor || $floor->trashed()){
                            return response(["error" => "بعض المعلومات خاطئة. قم بتعديلها وأعد المحاولة."]);
                        }
                    }
                    else{
                        return response(["error" => "بعض المعلومات خاطئة. قم بتعديلها وأعد المحاولة."]);
                    }
                    $places[] = ["floor" => $floor, "room" => $room];
                }
              $long_res_date = new LongReservationDate([
                "day_of_week" => $day,
                "from_time" => $from_time,
                "to_time" => $to_time,
                "event" => $event
              ]);

              $long_res_date->longReservation()->associate($long);
              $to_save[] = [$long_res_date, $places];
            }
          }

          $delete_ids = $to_delete->map(function($item){
            return $item->id;
          });
          LongReservationDate::destroy($delete_ids->all());
          foreach($to_save as $saveArr){
            $saveArr[0]->save();
            foreach($saveArr[1] as $place){
                $lrp = new LongReservationPlace();
                $lrp->floor()->associate($place["floor"]);
                if($place["room"]){
                    $lrp->room()->associate($place["room"]);
                }
                $lrp->longReservationDate()->associate($saveArr[0]);
                $lrp->save();
            }
          }
        }

        if($long->from_date != $from_date){
          $long->from_date = $from_date;
        }
        if($long->to_date != $to_date){
          $long->to_date = $to_date;
        }
        $long->save();
      }
      else{
        $temp = $reservation->temporaryReservation;
        if(count($fields)){
          $to_delete_dates = $reservation->temporaryReservation->temporaryReservationDates()->get();
          $to_delete_places = $reservation->temporaryReservation->temporaryReservationPlaces()->get();
          $to_save_dates = [];
          $to_save_places = [];
          for($i = 0; $i < 3; $i++){
            if(isset($fields["dates_$i"])){
              $from_time = $fields["from_time_$i"];
              $to_time = $fields["to_time_$i"];
              if(!validateTime($from_time) || !validateTime($to_time)){
                return response(["error" => "الوقت غير صالح."]);
              }
              if($from_time >= $to_time){
                return response(["error" => "بعض المعلومات خاطئة. قم بتعديلها وأعد المحاولة."]);
              }
              $date = $fields["date_$i"];
              $temp_res_date = new TemporaryReservationDate([
                "date" => $date,
                "from_time" => $from_time,
                "to_time" => $to_time
              ]);
              $temp_res_date->temporaryReservation()->associate($temp);
              $to_save_dates[] = $temp_res_date;
            }
          }

          foreach($fields["places"] as $place){
              $placeArr = explode("_", $place);
              $floor = null;
              $room = null;
              if(count($placeArr) > 1){
                  $floor = Floor::find($placeArr[0]);
                  if(!$floor || $floor->trashed()){
                      return response(["error" => __('المكان غير موجود')]);
                  }
                  $room = Room::find($placeArr[1]);
                  if(!$room || $room->trashed()){
                      return response(["error" => __('المكان غير موجود')]);
                  }
              }
              elseif(count($placeArr) == 1){
                  $floor = Floor::find($placeArr[0]);
                  if(!$floor || $floor->trashed()){
                      return response(["error" => __('المكان غير موجود')]);
                  }
              }
              else{
                  return response(["error" => "بعض المعلومات خاطئة. قم بتعديلها وأعد المحاولة."]);
              }
              $temp_res_place = new TemporaryReservationPlace();
              $temp_res_place->floor()->associate($floor);
              if($room){
                  if($room->trashed()){
                      return response(["error" => __('المكان غير موجود')]);
                  }
                  $temp_res_place->room()->associate($room);
              }
              $temp_res_place->temporaryReservation()->associate($temp);
              $to_save_places[] = $temp_res_place;
          }

          $delete_dates_ids = $to_delete_dates->map(function($item, $key){
            return $item->id;
          });

          $delete_places_ids = $to_delete_places->map(function($item, $key){
            return $item->id;
          });

          TemporaryReservationDate::destroy($delete_dates_ids->all());
          TemporaryReservationPlace::destroy($delete_places_ids->all());

          foreach($to_save_dates as $date){
            $date->save();
          }

          foreach($to_save_places as $place){
            $place->save();
          }
        }
      }
      $editedReservation = new EditedReservation();
      $editedReservation->reservation()->associate($reservation);
      $editedReservation->save();
      $user = Auth::user();
      if(!$reservation->user()->withTrashed()->first()->trashed() && $reservation->user->id !== $user->id){
        $url = url('/show-reservation/'.$reservation->id);
        if($reservation->user->isAdmin()){
          $url = url('/view-reservation/'.$reservation->id);
        }
        Notification::send($reservation->user,
                      new UserNotifications(__('تم تعديل الطلب ').$reservation->event_name,
                      $url));
      }
      $request->session()->flash('message', __("تم تعديل الطلب"));
      $request->session()->flash("message_class", "success");

      return response()->json(["success" => $reservation->id]);
    }

    /**
     * get reservations conflicting with a reservation request with new fields
     * @param Reservation $reservation
     * @param array $fields
     * @param string $from_date
     * @param string $to_date
     * @param null|string $type
     * @return array
     */
    private function getOtherReservationsWithFields($reservation, $fields, $from_date, $to_date, $type = null){
      if(($reservation && $reservation->longReservation) || ($type === "long")){
        $hasDays = false;
        for($day = 0; $day < 7; $day++){
          if(isset($fields["day_$day"])){
            $long = null;
            if($reservation){
              $long = $reservation->longReservation;
            }
            $from_time = $fields["from_time_$day"];
            $to_time = $fields["to_time_$day"];
            if(!validateTime($from_time) || !validateTime($to_time)){
              return ["error" => "الوقت غير صالح."];
            }
            if($from_time >= $to_time){
              return ["error" => "لا يمكن ان يكون وقت بداية النشاط بعد وقت نهايته"];
            }
            $places = [];
            if(!isset($fields["place_$day"]) || !count($fields["place_$day"])){
                return ["error" => "يجب إختيار مكان واحد على الأقل"];
            }
            foreach($fields["place_$day"] as $place){
                $arr = explode("_", $place);
                $floor = Floor::find($arr[0]);
                if(!$floor || $floor->trashed()){
                    return ["error" => __('المكان غير موجود')];
                }
                $room = null;
                if(count($arr) > 1){
                    $room = Room::find($arr[1]);
                    if(!$room || $room->trashed()){
                        return ["error" => __('المكان غير موجود')];
                    }
                }

                $places[] = ["floor" => $floor, "room" => $room];
            }
            if(count($places) == 0){
                return ["error" => __('يجب إختيار مكان واحد على الأقل')];
            }
            $j = $day == 6 ? 0 : $day + 1;
            $other_reservations[$day] = ["from_time" => $from_time, "to_time" => $to_time,
                                              "from_date" => $from_date, "to_date" => $to_date,
                                              "day_of_week" => $j, "reservations" => []];
            getOtherReservationsForLong($other_reservations, $day, $from_date, $to_date, $long, $j, $places, $from_time, $to_time);
            $hasDays = true;
          }
        }
        if(!$hasDays){
          return ["error" => "يجب إختيار يوم واحد على الأقل."];
        }
      }
      else{
        //get dates
        $temp = null;
        if($reservation){
          $temp = $reservation->temporaryReservation;
        }
        $places = [];
        foreach($fields["places"] as $place){
          $arr = explode("_", $place);
          $floor = Floor::find($arr[0]);
          if(!$floor || $floor->trashed()){
            return ["error" => __('المكان غير موجود')];
          }
          $room = null;
          if(count($arr) > 1){
            $room = Room::find($arr[1]);
            if(!$room || $room->trashed()){
              return ["error" => __('المكان غير موجود')];
            }

          }
          $places[] = ["floor" => $floor, "room" => $room];
        }
        if(!count($places)){
          return ["error" => "يجب إختيار مكان واحد على الأقل."];
        }
        $hasDates = false;
        for($i = 0; $i < 3; $i++){
          if(isset($fields["dates_$i"])){
            $date = $fields["date_$i"];
            $validator = Validator::make(["date" => $date], [
                "date" => 'required|date'
            ]);
            if($validator->fails()){
                return ["error" => "يجب تحديد تاريخ النشاط"];
            }
            $from_time = $fields["from_time_$i"];
            $to_time = $fields["to_time_$i"];
            if(!validateTime($from_time) || !validateTime($to_time)){
              return ["error" => "الوقت غير صالح."];
            }
            if($from_time >= $to_time){
              return ["error" => "لا يمكن ان يكون وقت بداية النشاط بعد وقت نهايته"];
            }
            $other_reservations[$i] = ["from_time" => $from_time, "to_time" => $to_time,
                                              "date" => $date, "reservations" => []];
            //dd($date, $from_time, $to_time);
            getOtherReservationsForTemp($other_reservations, $i, $date, $temp, $places, $from_time, $to_time);
            $hasDates = true;
          }
        }
        if(!$hasDates){
          return ["error" => "يجب إختيار يوم وتوقيت واحد على الأقل."];
        }


      }
      return $other_reservations;
    }

    /**
     * get reservations conflicting with a reservation (temporary or long)
     * @param Reservation $reservation
     * @return array
     */
    private function getOtherReservations(Reservation $reservation){
      $other_reservations = [];
      if($reservation->longReservation){
        $long = $reservation->longReservation;
        $dates = $long->longReservationDates;
        foreach($dates as $date){
          $other_reservations[$date->id] = ["from_time" => $date->from_time, "to_time" => $date->to_time,
                                            "from_date" => $long->from_date, "to_date" => $long->to_date,
                                            "day_of_week" => $date->day_of_week, "reservations" => []];
          $placesCollections = $date->longReservationPlaces()->get();
          $places = [];
          foreach($placesCollections as $place){
            $floor = $place->floor()->withTrashed()->first();
            $room = $place->room()->withTrashed()->first();
            $places[] = ["room" => $room, "floor" => $floor];
          }
          getOtherReservationsForLong($other_reservations, $date->id, $long->from_date, $long->to_date, $long, $date->day_of_week, $places, $date->from_time, $date->to_time);
        }
      }
      else{
        $temp = $reservation->temporaryReservation;
        $dates = $temp->temporaryReservationDates()->get();
        $placesCollection = $temp->temporaryReservationPlaces()->get();
        $places = [];
        foreach($placesCollection as $place){
          $floor = $place->floor()->withTrashed()->first();
          $room = $place->room()->withTrashed()->first();
          $places[] = ["floor" => $floor, "room" => $room];
        }
        foreach($dates as $date){
          $other_reservations[$date->id] = ["from_time" => $date->from_time, "to_time" => $date->to_time,
                                            "date" => $date->date, "reservations" => []];
          getOtherReservationsForTemp($other_reservations, $date->id, $date->date, $temp, $places, $date->from_time, $date->time);
        }
      }
      return $other_reservations;
    }

    /**
     * view a reservation for admin
     * @param int $id
     * @return mixed
     */
    public function viewReservationAdmin($id){
      $reservation = Reservation::find($id);
      $user = Auth::user();
      if(!$reservation){
        abort(404);
      }

      $reservation->load(["longReservation", "longReservation.longReservationDates",
                          "longReservation.longReservationDates.longReservationPlaces.floor",
                            "longReservation.longReservationDates.longReservationPlaces.room",
                          "temporaryReservation",
                          "temporaryReservation.temporaryReservationDates",
                          "temporaryReservation.temporaryReservationPlaces",
                          "user", "pausedReservation"]);
      $floor = Floor::all();
      $room = Room::all();
      return view("view-reservation")->withReservation($reservation)->withUser($user)
                                      ->withFloors($floor)->withRooms($room);
    }

    /**
     * get list of user's reservation
     * @param Request $request
     * @return mixed
     */
    public function getUserReservations(Request $request){
      $user = Auth::user();
      $reservations = $user->reservations()->get()->reject(function($value){
        return $value->isEditRequest != null && $value->isEditRequest->reservation->is_approved != -1;
      });
      $reservations->load("editedReservation");
      $reservations = $reservations->reverse();
      $reservations = paginate($reservations, 15, $request->path());
      return view("my-reservations")->withReservations($reservations)->withUser($user);
    }

    /**
     * view reservation for user
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function viewReservation($id){
      $reservation = Reservation::find($id);
      $user = Auth::user();
      if($user->isAdmin()){
          return redirect("/view-reservation/$id");
      }
      if(!$reservation || ($reservation->is_approved != 1 &&
                            $reservation->user()->withTrashed()->first()->id != $user->id && !$user->isAdmin() &&
                            !$user->is_maintainer)){
        abort(404);
      }

      if($reservation->is_approved == 0 && $reservation->isEditRequest){
        return redirect("/");
      }

      $reservation->load(["longReservation", "longReservation.longReservationDates",
                            "longReservation.longReservationDates.longReservationPlaces.floor",
                            "longReservation.longReservationDates.longReservationPlaces.room",
                          "temporaryReservation",
                          "temporaryReservation.temporaryReservationDates",
                          "temporaryReservation.temporaryReservationPlaces",
                          "user", "pausedReservation"]);
      return view("view-reservation")->withReservation($reservation)->withUser($user);
    }

    /**
     * post - delete a reservation
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function deleteReservation(Request $request, $id){
      $reservation = Reservation::find($id);
      $user = Auth::user();
      if(!$reservation || $reservation->is_approved == -2 ||
          ($reservation->user()->withTrashed()->first()->id != $user->id && !$user->isAdmin())){
        abort(404);
      }

      if($reservation->is_approved == 0 && $reservation->isEditRequest){
        return redirect("/");
      }

      if($reservation->is_approved == 1){
        if(!$user->trashed()){
          if($user->isAdmin()){
            $url = url('/my-reservation/'.$reservation->id);
            $reservation->is_approved = -2;
            $reservation->save();
            $reservation->pausedReservation()->delete();
            if($reservation->deleteRequest){
                $reservation->deleteRequest->delete();
            }
            $request->session()->flash('message', __("تم إلغاء الطلب."));
            $request->session()->flash("message_class", "success");
            if(!$reservation->user()->withTrashed()->first()->trashed()){
                if($reservation->user->isAdmin()){
                    $url = url('/new-reservations/'.$reservation->id);
                }
                Notification::send($reservation->user,
                    new UserNotifications(__('تم إلغاء الطلب ').$reservation->event_name,
                    $url));
            }
          }
          else{
              //send a delete request to be approved by admin
            if(!$reservation->deleteRequest){
              $deleteRequest = new DeleteRequest();
              $deleteRequest->reservation()->associate($reservation);
              $deleteRequest->save();
            }

            $request->session()->flash('message', __("تم إرسال طلب إلغاء النشاط."));
            $request->session()->flash("message_class", "success");
              $adminUsers = User::where("is_admin", "1")->get();
              $adminUsers->each(function($value) use($reservation) {
                  Notification::send($value,
                      new UserNotifications(__('طلب إلغاء حجز جديد'),
                          "/reservation/".$reservation->id));
              });
          }
        }
      }
      else{
        $reservation_user = $reservation->user()->withTrashed()->first();
        $reservation_event_name = $reservation->event_name;
        $reservation->delete();
        $request->session()->flash('message', __("تم حذف الطلب."));
        $request->session()->flash("message_class", "success");
        if(!$user->trashed()){
            if(!$reservation_user->trashed() && $reservation_user->id != $user->id){
                $url = url('/my-reservation/'.$reservation->id);
                if($reservation_user->isAdmin()){
                    $url = url('/new-reservations/'.$reservation->id);
                }
                Notification::send($reservation_user,
                    new UserNotifications(__('تم حذف الطلب ').$reservation_event_name,
                    $url));
            }
        }
      }
      if($user->isAdmin()){
        return redirect('/new-reservations');
      }
      else{
        return redirect("/my-reservations");
      }
    }

    /**
     * approve edit by admin (for user)
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function approveEdit(Request $request, $id){
      $reservation = Reservation::find($id);
      $user = Auth::user();
      if(!$reservation || $reservation->user()->withTrashed()->first()->trashed() || $reservation->user->id != $user->id){
        abort(404);
      }
      if(!$reservation->editedReservation){
        return redirect("/");
      }
      if($reservation->longReservation){
        $lrds = $reservation->longReservation->longReservationDates()->get();
        foreach($lrds as $item){
          foreach($item->longReservationPlaces()->get() as $lrp){
              $floor = $lrp->floor()->withTrashed()->first();
              $room = $lrp->room()->withTrashed()->first();
              if(!$floor || $floor->trashed() || ($room && $room->trashed())){
                  $request->session()->flash('message', __("هذا المكان غير موجود"));
                  $request->session()->flash("message_class", "danger");
                  return back();
              }
          }
        }
      }
      else{
        $trds = $reservation->temporaryReservation->temporaryReservationPlaces()->get();
        foreach($trds as $item){
          if($item->floor()->withTrashed()->first()->trashed() || $item->room()->withTrashed()->first()->trashed()){
            $request->session()->flash('message', __("هذا المكان غير موجود"));
            $request->session()->flash("message_class", "danger");
            return back();
          }
        }
      }
      $reservation->editedReservation->delete();
      $reservation->is_approved = 0;
      $reservation->save();
      $request->session()->flash('message', __("تم إرسال موافقتك على التعديل."));
      $request->session()->flash("message_class", "success");
      return redirect("/my-reservations");
    }

    /**
     * show edit reservation form (for user)
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function editReservation($id){
      $reservation = Reservation::find($id);
      $user = Auth::user();
      if(!$reservation || $reservation->is_approved == -2 || $reservation->user()->withTrashed()->first()->trashed() || $reservation->user->id != $user->id){
        abort(404);
      }

      if(($reservation->is_approved && $reservation->hasEditRequest &&
          $reservation->hasEditRequest->newReservation->is_approved != -1) ||
          ($reservation->is_approved == 0 && $reservation->isEditRequest) ||
          ($reservation->pausedReservation)){
        return redirect("/");
      }

      $reservation->load(["longReservation", "longReservation.longReservationDates",
          "longReservation.longReservationDates.longReservationPlaces.floor",
          "longReservation.longReservationDates.longReservationPlaces.room",
                          "temporaryReservation",
                          "temporaryReservation.temporaryReservationDates",
                          "temporaryReservation.temporaryReservationPlaces",
                          "user"]);
      $floors = Floor::all();
      $rooms = Room::all();

      return view("edit-reservation")->withReservation($reservation)->withUser($user)
                                    ->withFloors($floors)->withRooms($rooms);
    }

    /**
     * post - edit a reservation request or send a request to edit approved reservation
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function postEditReservation(Request $request, $id){
      $reservation = Reservation::find($id);
      $user = Auth::user();
      if(!$reservation || $reservation->is_approved == -2 || $reservation->user()->withTrashed()->first()->trashed() || $reservation->user->id != $user->id){
        abort(404);
      }

      if(($reservation->is_approved && $reservation->hasEditRequest &&
          $reservation->hasEditRequest->newReservation->is_approved != -1) ||
          ($reservation->is_approved == 0 && $reservation->isEditRequest) ||
          ($reservation->pausedReservation)){
        return redirect("/");
      }
      $reservation->load(["longReservation", "longReservation.longReservationDates",
                            "longReservation.longReservationDates.longReservationPlaces.floor",
                            "longReservation.longReservationDates.longReservationPlaces.room",
                            "temporaryReservation",
                            "temporaryReservation.temporaryReservationDates",
                            "temporaryReservation.temporaryReservationPlaces",
                            "user"]);

      Validator::make($request->all(), [
        "committee" => 'required',
        'event_name' => 'required',
        'pledge' => 'required'
      ])->validate();

      $committee = $request->committee;
      $event_name = $request->event_name;
      $notes = $request->notes;
      $supervisors = $request->supervisors;

      if($reservation->longReservation){
        //long reservation
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        if(!$from_date){
          return back()->withErrors(["from_date" => "تاريخ البداية مطلوب"]);
        }
        if(!$to_date){
          return back()->withErrors(["to_date" => "تاريخ النهاية مطلوب"]);
        }
        if($from_date >= $to_date){
          return back()->withErrors(["from_date" => "لا يمكن أن يكون تاريخ البداية بعد أو في نفس تاريخ النهاية."]);
        }
        $dates = [];
        $fields = [];
        for($i=0; $i < 7; $i++){
          if($request->input("day_$i") !== null){
            $from_time = $request->input("from_time_$i");
            $to_time = $request->input("to_time_$i");
            $event = $request->input("event_$i");
            $placesField = $request->input("place_$i");
            if(!validateTime($from_time) || !validateTime($to_time)){
              return back()->withErrors(["error" => "الوقت غير صالح."]);
            }
            if($from_time >= $to_time){
              return back()->withErrors(["from_time_$i" => "لا يمكن أن يكون وقت بداية النشاط في نفس وقت النهاية."]);
            }
            $places = [];
            foreach($placesField as $place){
                $placeArr = explode("_", $place);
                $count = count($placeArr);
                $floor = null;
                $room = null;
                if($count == 1){
                    $floor = Floor::find($place);
                    if(!$floor || $floor->trashed()){
                        return back()->withErrors(["place_$i" => "المكان غير موجود"]);
                    }
                }
                elseif($count == 2){
                    $floor = Floor::find($placeArr[0]);
                    if(!$floor || $floor->trashed()){
                        return back()->withErrors(["place_$i" => "المكان غير موجود"]);
                    }
                    $room = Room::find($placeArr[1]);
                    if(!$room || $room->trashed()){
                        return back()->withErrors(["place_$i" => "المكان غير موجود"]);
                    }
                }
                else{
                    return back()->withErrors(["place_$i" => "المكان غير موجود"]);
                }
                $places[] = ["floor" => $floor, "room" => $room];
            }
            if(count($places) == 0){
                return back()->withErrors(["place_$i" => "يجب إختيار مكان واحد على الأقل"]);
            }
            $day = $i === 6 ? 0 : $i + 1;
            $dates[$day] = ["from_time" => $from_time,
                        "to_time" => $to_time, "event" => $event, "places" => $places];
            $fields["day_$i"] = true;
            $fields["from_time_$i"] = $from_time;
            $fields["to_time_$i"] = $to_time;
            $fields["place_$i"] = $placesField;
          }
        }
        if(!count($dates)){
          return back()->withErrors(["error" => "يجب إختيار توقيت واحد على الأقل."]);
        }
        $other_reservations = $this->getOtherReservationsWithFields($reservation, $fields, $from_date, $to_date);
        if(isset($other_reservations["error"])){
            return back()->withErrors($other_reservations);
        }
        elseif(count($other_reservations) > 0){
            foreach($other_reservations as $or){
                if(count($or["reservations"]) > 0){
                    return back()->withErrors(["error" => "هناك نشاطات اخرى في نفس الوقت أو نفس المكان. قم بتعديل المعلومات وحاول مرة أخرى."]);
                }
            }
        }
        if($reservation->is_approved){
          $new_reservation = new Reservation([
            "committee" => $committee,
            "event_name" => $event_name,
            "notes" => $notes,
            "supervisors" => $supervisors,
            "date_created" => $reservation->date_created,
            "is_approved" => 0
          ]);
          $new_reservation->user()->associate($user);
          $new_reservation->save();
          $new_long = new LongReservation([
            "from_date" => $from_date,
            "to_date" => $to_date,
          ]);
          $new_long->reservation()->associate($new_reservation);
          $new_long->save();
          foreach ($dates as $day => $date) {
            $lrd = new LongReservationDate([
              "day_of_week" => $day,
              "from_time" => $date["from_time"],
              "to_time" => $date["to_time"],
              "event" => $date["event"]
            ]);
            $lrd->longReservation()->associate($new_long);
            $lrd->save();
            foreach($date["places"] as $place) {
                $lrp = new LongReservationPlace();
                $lrp->floor()->associate($place["floor"]);
                if($place["room"]){
                    $lrp->room()->associate($place["room"]);
                }
                $lrp->longReservationDate()->associate($lrd);
                $lrp->save();
            }
          }
          $editRequest = new EditRequest();
          $editRequest->reservation()->associate($reservation);
          $editRequest->newReservation()->associate($new_reservation);
          $editRequest->save();
          $request->session()->flash('message', __("تم إرسال التعديل بنجاح"));
          $request->session()->flash("message_class", "success");
            $adminUsers = User::where("is_admin", "1")->get();
            $adminUsers->each(function($value) use($new_reservation) {
                Notification::send($value,
                    new UserNotifications(__('طلب حجز جديد'),
                        "/reservation/".$new_reservation->id));
            });
        }
        else{
          $reservation->committee = $committee;
          $reservation->event_name = $event_name;
          $reservation->notes = $notes;
          $reservation->supervisors = $supervisors;
          $reservation->is_approved = 0;
          $reservation->save();
          $long = $reservation->longReservation;
          $long->from_date = $from_date;
          $long->to_date = $to_date;
          $long->save();
          $lrd = $long->longReservationDates()->get();
          $lrdGrouped = $lrd->groupBy("day_of_week");
          $lrdArray = $lrdGrouped->toArray();
          $to_edit = array_intersect_key($lrdArray, $dates);
          $to_delete = array_diff_key($lrdArray, $dates);
          $to_add = array_diff_key($dates, $lrdArray);
          foreach($to_edit as $day => $longDate){
            $date = $dates[$day];
            $longDate = $lrd->where("id", $longDate[0]["id"])->first();
            $longDate->from_time = $date["from_time"];
            $longDate->to_time = $date["to_time"];
            $longDate->event = $date["event"];
              $longDate->save();
              $longDate->longReservationPlaces()->delete();
              foreach($date["places"] as $place){
                  $lrp = new LongReservationPlace();
                  $lrp->floor()->associate($place["floor"]);
                  if($place["room"]){
                      $lrp->room()->associate($place["room"]);
                  }
                  $lrp->longReservationDate()->associate($longDate);
                  $lrp->save();
              }
          }

          foreach($to_delete as $longDate){
            $longDate = $lrd->where("id", $longDate[0]["id"])->first();
            $longDate->delete();
          }

          foreach($to_add as $day => $date){
            $longDate = new LongReservationDate([
              "from_time" => $date["from_time"],
              "to_time" => $date["to_time"],
              "event" => $date["event"],
              "day_of_week" => $day,
            ]);
            $longDate->longReservation()->associate($long);
            $longDate->save();
              foreach($date["places"] as $place){
                  $lrp = new LongReservationPlace();
                  $lrp->floor()->associate($place["floor"]);
                  if($place["room"]){
                      $lrp->room()->associate($place["room"]);
                  }
                  $lrp->longReservationDate()->associate($longDate);
                  $lrp->save();
              }
          }
        }
      }
      else{
        //temporary reservation
        $equipment_needed = [];
        $dates = [];
        foreach($request->input("equipment_needed") as $en){
          $equipment_needed[] = $en;
        }
        $fields = [];
        for($i = 0; $i < 3; $i++){
          if($request->input("dates_$i") !== null){
            $date = $request->input("date_$i");
            if(!$date){
              return back()->withErrors(["date_$i" => "اليوم غير محدد"]);
            }
            $from_time = $request->input("from_time_$i");
            $to_time = $request->input("to_time_$i");
            if(!validateTime($from_time) || !validateTime($to_time)){
              return back()->withErrors(["error" => "الوقت غير صالح."]);
            }
            if($from_time >= $to_time){
              return back()->withErrors(["from_time_$i" => "لا يمكن أن يكون وقت البداية بعد أو في نفس وقت النهاية للنشاط."]);
            }
            $dates[] = ["date" => $date, "from_time" => $from_time, "to_time" => $to_time];
            $fields["dates_$i"] = true;
            $fields["date_$i"] = $date;
            $fields["from_time_$i"] = $from_time;
            $fields["to_time_$i"] = $to_time;
          }
        }
        if(!count($dates)){
          return back()->withErrors(["error" => "يجب إختيار وقت واحد على الأقل."]);
        }
        $places = [];
        foreach($request->input('places') as $place){
            $placeArr = explode("_", $place);
            $count = count($placeArr);
            $floor = null;
            $room = null;
            if($count == 1){
                $floor = Floor::find($place);
                if(!$floor || $floor->trashed()){
                    return back()->withErrors(["places" => "المكان غير موجود"]);
                }
            }
            elseif($count == 2){
                $floor = Floor::find($placeArr[0]);
                if(!$floor || $floor->trashed()){
                    return back()->withErrors(["places" => "المكان غير موجود"]);
                }
                $room = Room::find($placeArr[1]);
                if(!$room || $room->trashed()){
                    return back()->withErrors(["places" => "المكان غير موجود"]);
                }
            }
            else{
                return back()->withErrors(["places" => "المكان غير موجود"]);
            }
            $places[] = ["floor" => $floor, "room" => $room];
            $fields["places"][] = $place;
        }
        if(!count($places)){
          return back()->withErrors(["error" => "يجب إختيار مكان واحد على الأقل."]);
        }
        $other_reservations = $this->getOtherReservationsWithFields($reservation, $fields, null, null);
          if(isset($other_reservations["error"])){
              return back()->withErrors($other_reservations);
          }
          elseif(count($other_reservations) > 0){
              foreach($other_reservations as $or){
                  if(count($or["reservations"]) > 0){
                      return back()->withErrors(["error" => "هناك نشاطات اخرى في نفس الوقت أو نفس المكان. قم بتعديل المعلومات وحاول مرة أخرى."]);
                  }
              }
          }
          if($reservation->is_approved == 1){
          $new_reservation = new Reservation([
            "committee" => $committee,
            "event_name" => $event_name,
            "notes" => $notes,
            "supervisors" => $supervisors,
            "date_created" => $reservation->date_created,
            "is_approved" => 0
          ]);
          $new_reservation->user()->associate($user);
          $new_reservation->save();
          $temp = new TemporaryReservation();
          $i = 1;
          foreach($equipment_needed as $en){
            switch ($i) {
              case 1:
                $temp->equipment_needed_1 = $en;
                break;
              case 2:
                $temp->equipment_needed_2 = $en;
                break;
              case 3:
                $temp->equipment_needed_3 = $en;
                break;
            }
            $i++;
          }
          $temp->reservation()->associate($new_reservation);
          $temp->save();
          foreach ($dates as $date) {
            $temp_date = new TemporaryReservationDate([
              "date" => $date["date"],
              "from_time" => $date["from_time"],
              "to_time" => $date["to_time"]
            ]);
            $temp_date->temporaryReservation()->associate($temp);
            $temp_date->save();
          }
          foreach($places as $place){
            $temp_place = new TemporaryReservationPlace();
            $temp_place->floor()->associate($place["floor"]);
            if($place["room"]){
              $temp_place->room()->associate($place["room"]);
            }
            $temp_place->temporaryReservation()->associate($temp);
            $temp_place->save();
          }
          $editRequest = new EditRequest();
          $editRequest->reservation()->associate($reservation);
          $editRequest->newReservation()->associate($new_reservation);
          $editRequest->save();
          $request->session()->flash('message', __("تم إرسال التعديل بنجاح"));
          $request->session()->flash("message_class", "success");
            $adminUsers = User::where("is_admin", "1")->get();
            $adminUsers->each(function($value) use($new_reservation) {
                Notification::send($value,
                    new UserNotifications(__('طلب حجز جديد'),
                        "/reservation/".$new_reservation->id));
            });
        }
        else{
          $reservation->committee = $committee;
          $reservation->event_name = $event_name;
          $reservation->notes = $notes;
          $reservation->supervisors = $supervisors;
          $reservation->is_approved = 0;
          $reservation->save();
          $temp = $reservation->temporaryReservation;
          $i = 1;
          $temp->equipment_needed_1 = null;
          $temp->equipment_needed_2 = null;
          $temp->equipment_needed_3 = null;
          foreach($equipment_needed as $en){
            switch ($i) {
              case 1:
                $temp->equipment_needed_1 = $en;
                break;
              case 2:
                $temp->equipment_needed_2 = $en;
                break;
              case 3:
                $temp->equipment_needed_3 = $en;
                break;
            }
            $i++;
          }
          $temp->save();
          $temp_dates = $temp->temporaryReservationDates()->get();
          foreach ($temp_dates as $td) {
            $td->delete();
          }
          foreach($dates as $date){
            $temp_date = new TemporaryReservationDate([
              "date" => $date["date"],
              "from_time" => $date["from_time"],
              "to_time" => $date["to_time"]
            ]);
            $temp_date->temporaryReservation()->associate($temp);
            $temp_date->save();
          }
          $temp_places = $temp->temporaryReservationPlaces()->get();
          foreach ($temp_places as $tp) {
            $tp->delete();
          }
          foreach($places as $place){
            $temp_place = new TemporaryReservationPlace();
            $temp_place->floor()->associate($place["floor"]);
            if($place["room"]){
              $temp_place->room()->associate($place["room"]);
            }
            $temp_place->temporaryReservation()->associate($temp);
            $temp_place->save();
          }
          $request->session()->flash('message', __("تم التعديل بنجاح."));
          $request->session()->flash("message_class", "success");
        }
      }
      if($reservation->editedReservation){
        $reservation->editedReservation->delete();
      }
      return redirect("/my-reservations");
    }

    /**
     * approve reservation request (for admin)
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function approveReservation(Request $request, $id){
      $reservation = Reservation::find($id);
      if(!$reservation || $reservation->is_approved == -2 || ($reservation->is_approved == 1 && !$reservation->hasEditRequest) ||
          $reservation->is_approved != 0){
        abort(404);
      }
      $reservation->load(["longReservation", "longReservation.longReservationDates",
                            "longReservation.longReservationDates.longReservationPlaces.floor",
                            "longReservation.longReservationDates.longReservationPlaces.room",
                          "temporaryReservation",
                          "temporaryReservation.temporaryReservationDates",
                          "temporaryReservation.temporaryReservationPlaces",
                          "user"]);
      $redirectId = $id;
      if($reservation->user()->withTrashed()->first()->trashed()){
          $request->session()->flash('message', __("المستخدم غير موجود."));
          $request->session()->flash("message_class", "danger");
          return back();
      }
      if($reservation->isEditRequest){
        //approve edit request
        $toEditReservation = $reservation->isEditRequest->reservation;
        $toEditReservation->committee = $reservation->committee;
        $toEditReservation->supervisors = $reservation->supervisors;
        $toEditReservation->notes = $reservation->notes;
        $toEditReservation->event_name = $reservation->event_name;
        if($toEditReservation->is_approved == -1 && $toEditReservation->reservationRejection){
          $toEditReservation->reservationsRejection->delete();
        }
        $toEditReservation->save();
        if($reservation->temporaryReservation){
          $temp = $toEditReservation->temporaryReservation;
          $temp->equipment_needed_1 = $reservation->temporaryReservation->equipment_needed_1;
          $temp->equipment_needed_2 = $reservation->temporaryReservation->equipment_needed_2;
          $temp->equipment_needed_3 = $reservation->temporaryReservation->equipment_needed_3;
          $temp->save();
          $tempDates = $temp->temporaryReservationDates()->get();
          foreach($tempDates as $date){
            $date->delete();
          }
          $tempPlaces = $temp->temporaryReservationPlaces()->get();
          foreach($tempPlaces as $place){
            $place->delete();
          }
          $tempDates = $reservation->temporaryReservation->temporaryReservationDates()->get();
          foreach($tempDates as $date){
            $date->temporaryReservation()->dissociate();
            $date->temporaryReservation()->associate($temp);
            $date->save();
          }
          $tempPlaces = $reservation->temporaryReservation->temporaryReservationPlaces()->get();
          foreach($tempPlaces as $place){
            if($place->floor->trashed() || ($place->room && $place->room->trashed())){
              $request->session()->flash('message', __("المكان محذوف"));
              $request->session()->flash("message_class", "danger");
              return back();
            }
            $place->temporaryReservation()->dissociate();
            $place->temporaryReservation()->associate($temp);
            $place->save();
          }
        }
        else{
          $long = $toEditReservation->longReservation;
          $long->from_date = $reservation->longReservation->from_date;
          $long->to_date = $reservation->longReservation->to_date;
          $long->save();
          $longDates = $long->longReservationDates()->get();
          foreach($longDates as $date){
            $date->delete();
          }
          $longDates = $reservation->longReservation->longReservationDates()->get();
          foreach($longDates as $date){
            foreach($date->longReservationPlaces()->get() as $place){
                $floor = $place->floor()->withTrashed()->first();
                $room = $place->room()->withTrashed()->first();
                if((!$floor || $floor->trashed()) || ($room && $room->trashed())){
                    $request->session()->flash('message', __("المكان محذوف"));
                    $request->session()->flash("message_class", "danger");
                    return back();
                }
            }
              $date->longReservation()->dissociate();
              $date->longReservation()->associate($long);
              $date->save();
          }
        }
        $reservation->delete();
        $toEditReservation->is_approved = 1;
          $message = $request->input('approve_message');
          if($message !== $toEditReservation->message) {
              $toEditReservation->message = $message;
          }
        $toEditReservation->save();
        $redirectId = $toEditReservation->id;
        if(!$toEditReservation->user()->withTrashed()->first()->trashed()){
          $url = url('/show-reservation/'.$toEditReservation->id);
          if($toEditReservation->user->isAdmin()){
            $url = url('/view-reservation/'.$toEditReservation->id);
          }
          Notification::send($toEditReservation->user,
              new UserNotifications("تم الموافقة على تعديل الحجز ".$toEditReservation->event_name.
                  ($message ? ' <strong>بشروط محددة</strong>' : ''), $url));
        }
        $request->session()->flash('message', __("تم الموافقة على تعديل الحجز"));
        $request->session()->flash("message_class", "success");
      }
      else{
          //check if places are still available
          if($reservation->longReservation){
              //long reservation
              $lrds = $reservation->longReservation->longReservationDates()->get();
              foreach($lrds as $lrd){
                  foreach($lrd->longReservationPlaces()->get() as $place){
                      $floor = $place->floor()->withTrashed()->first();
                      $room = $place->room()->withTrashed()->first();
                      if(($floor && $floor->trashed()) || ($room && $room->trashed())){
                          $request->session()->flash('message', __("المكان غير موجود"));
                          $request->session()->flash("message_class", "danger");
                          return back();
                      }
                  }
              }
          }
          else{
              //temporary reservation
              $trps = $reservation->temporaryReservation->temporaryReservationPlaces()->get();
              foreach ($trps as $trp){
                  $floor = $trp->floor()->withTrashed()->first();
                  $room = $trp->room()->withTrashed()->first();
                  if(($floor && $floor->trashed()) || ($room && $room->trashed())){
                      $request->session()->flash('message', __("المكان غير موجود"));
                      $request->session()->flash("message_class", "danger");
                      return back();
                  }
              }
          }
        $reservation->is_approved = 1;
          $message = $request->input('approve_message');
          if($message !== $reservation->message) {
              $reservation->message = $message;
          }
          $message = $request->input('approve_message');
          if($message !== $reservation->message) {
              $reservation->message = $message;
          }
        $reservation->save();
        if(!$reservation->user()->withTrashed()->first()->trashed()){
          $url = url('/show-reservation/'.$reservation->id);
          if($reservation->user->isAdmin()){
            $url = url('/view-reservation/'.$reservation->id);
          }
          Notification::send($reservation->user,
              new UserNotifications("تم الموافقة على حجز ".$reservation->event_name.
                  ($message ? ' <strong>بشروط محددة</strong>' : ''), $url));
        }
        $request->session()->flash('message', __("تم الموافقة على الحجز"));
        $request->session()->flash("message_class", "success");
      }

      return redirect("/view-reservation/$redirectId");
    }

    /**
     * reject a request by user (for admin)
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function rejectRequest(Request $request, $id){
      $reservation = Reservation::find($id);
      if(!$reservation || $reservation->is_approved == -2 || ($reservation->is_approved != 0 && !$reservation->deleteRequest) ||
          ($reservation->is_approved == 1 && !$reservation->isEditRequest)){
            abort(404);
      }
      if($reservation->deleteRequest){
        $reservation->deleteRequest->delete();
        $url = url('/show-reservation/'.$reservation->id);
        if(!$reservation->user()->withTrashed()->first()->trashed()){
            Notification::send($reservation->user,
                new UserNotifications(__('تم رفض إلغاء حجز ').$reservation->event_name,
                    $url));
        }
        $request->session()->flash('message', __("تم إلغاء طلب الحذف"));
        $request->session()->flash("message_class", "success");
      }
      else{
        if($reservation->isEditRequest && $reservation->is_approved == 1){
          $reservation->isEditRequest->newReservation->delete();
        }
        else{
          $reservation->is_approved = -1;
          $reservation->save();
        }
        if(!$reservation->user()->withTrashed()->first()->trashed()){
            $url = url('/show-reservation/'.$reservation->id);
            if($reservation->user->isAdmin()){
                $url = url('/view-reservation/'.$reservation->id);
            }
            Notification::send($reservation->user, new UserNotifications("تم إلغاء طلبك للحجز ".$reservation->event_name,
                $url));
        }
        $request->session()->flash('message', __("تم إلغاء الحجز"));
        $request->session()->flash("message_class", "success");
      }

      return redirect("/new-reservations");
    }

    /**
     * reject reservation (for admin)
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function rejectReservation(Request $request, $id){
      $reservation = Reservation::find($id);
      if(!$reservation || $reservation->is_approved == -2 || ($reservation->is_approved == 1 && !$reservation->isEditRequest)){
            abort(404);
      }
      if($reservation->isEditRequest){
        $reservation->isEditRequest->newReservation->delete();
      }
      else{
        $reservation->is_approved = -1;
        $reservation->save();
        if($reservation->pausedReservation){
            $reservation->pausedReservation->delete();
        }
      }
      if(null !== $request->input('rejection_message') && !$reservation->isEditRequest){
        $reservationRejection = $reservation->reservationsRejection;
        if($reservationRejection){
          $reservationRejection->delete();
        }
        $reservationRejection = new ReservationsRejection([
          "message" => $request->rejection_message
        ]);
        $reservationRejection->reservation()->associate($reservation);
        $reservationRejection->save();
      }
      if(!$reservation->user()->withTrashed()->first()->trashed()){
          $url = url('/show-reservation/'.$reservation->id);
          if($reservation->user->isAdmin()){
              $url = url('/view-reservation/'.$reservation->id);
          }
          Notification::send($reservation->user, new UserNotifications("تم إلغاء الحجز ".$reservation->event_name,
              $url));
      }
      $request->session()->flash('message', __("تم إلغاء الحجز"));
      $request->session()->flash("message_class", "success");

      return redirect("/new-reservations");
    }

    /**
     * show add reservation form (for user and admin)
     * @param $type
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addReservation($type){
      $user = Auth::user();
      if($type != "long" && $type != "temporary"){
        abort(404);
      }
      $floors = Floor::all();
      $rooms = Room::all();
      return view("add-reservation", ["user" => $user, "floors" => $floors, "rooms" => $rooms,
                                      "type" => $type]);
    }

    /**
     * check reservations conflicting with a new reservation (not added yet) (for admin)
     * @param Request $request
     * @param $type
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function checkNewReservation(Request $request, $type){
      if($type != "long" && $type != "temporary"){
        abort(404);
      }
      $fields = $request->fields;
      $from_date = $request->from_date;
      $to_date = $request->to_date;
      if(!$fields){
        return response()->json(["error" => "المعلومات خاطئة."]);
      }
      if($type == "long"){
        if(!$from_date || !$to_date){
          return response()->json(["error" => "المعلومات خاطئة."]);
        }
        if($from_date >= $to_date){
          return response()->json(["error" => "لا يمكن تاريخ البداية بعد تاريخ النهاية"]);
        }
      }
      $other_reservations = $this->getOtherReservationsWithFields(null, $fields, $from_date,
                            $to_date, $type);
      if(isset($other_reservations["error"])){
        return response()->json($other_reservations);
      }
      return response()->json(["reservations" => $other_reservations]);
    }

    /**
     * add reservation (for user and admin)
     * @param Request $request
     * @param $type
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function postAddReservation(Request $request, $type){
        //dd($request->all());
      if($type != "long" && $type != "temporary"){
        abort(404);
      }
      $user = Auth::user();
      Validator::make($request->all(), [
        "committee" => 'required',
        "event_name" => "required",
        "pledge" => 'accepted'
      ])->validate();
      $reservation = new Reservation([
        "committee" => $request->committee,
        "event_name" => $request->event_name,
        "notes" => $request->notes,
        "supervisors" => $request->supervisors,
        "is_approved" => $user->isAdmin() ? 1 : 0
      ]);
      $reservation->user()->associate($user);
      if($type == "long"){
        Validator::make($request->all(), [
          "from_date" => "date|required",
          "to_date" => "date|required"
        ])->validate();
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        if($from_date >= $to_date){
          return back()->withErrors(["from_date" => "لا يمكن أن يكون تاريخ البداية في نفس أو بعد تاريخ النهاية."]);
        }
        $fields = [];
        for($i = 0; $i < 7; $i++){
          if($request->input("day_$i") !== null){
            $fields["day_$i"] = true;
            $fields["from_time_$i"] = $request->input("from_time_$i");
            $fields["to_time_$i"] = $request->input("to_time_$i");
            $fields["event_$i"] = $request->input("event_$i");
            $fields["place_$i"] = $request->input("place_$i");
          }
        }
        //validation of place and from time/to time is done in the function
        $other_reservations = $this->getOtherReservationsWithFields(null, $fields, $from_date, $to_date, $type);
        if(isset($other_reservations["error"])){
          return back()->withErrors($other_reservations);
        }
        elseif(count($other_reservations) > 0){
            foreach($other_reservations as $or){
                if(count($or["reservations"]) > 0){
                    return back()->withErrors(["error" => "هناك نشاطات اخرى في نفس الوقت أو نفس المكان. قم بتعديل المعلومات وحاول مرة أخرى."]);
                }
            }
        }

        //all validated
        $reservation->save();
        $long = new LongReservation([
          "from_date" => $from_date,
          "to_date" => $to_date
        ]);
        $long->reservation()->associate($reservation);
        $long->save();
        for($i = 0; $i < 7; $i++){
          if(isset($fields["day_$i"])){
            $lrd = new LongReservationDate([
              "day_of_week" => $i == 6 ? 0 : $i + 1,
              "from_time" => $fields["from_time_$i"],
              "to_time" => $fields["to_time_$i"],
              "event" => $fields["event_$i"]
            ]);
            $lrd->longReservation()->associate($long);
            $lrd->save();
            foreach($fields["place_$i"] as $place){
                $placesArr = explode("_", $place);
                $floor = Floor::find($placesArr[0]);
                if(!$floor || $floor->trashed()){
                    return back()->withErrors(['floor' => __('المكان غير موجود')]);
                }
                $room = null;
                if(count($placesArr) == 2){
                    $room = Room::find($placesArr[1]);
                    if(!$room || $room->trashed()){
                        return back()->withErrors(['room' => __('المكان غير موجود')]);
                    }
                }
                $lrp = new LongReservationPlace();
                $lrp->floor()->associate($floor);
                if($room){
                    $lrp->room()->associate($room);
                }
                $lrp->longReservationDate()->associate($lrd);
                $lrp->save();
            }
          }
        }
      }
      else{
        //temp reservation
        //time & date
        for($i = 0; $i < 3; $i++){
          if(null !== $request->input("dates_$i")){
            $date = $request->input("date_$i");
            Validator::make(["date" => $date], [
              "date" => 'required|date'
            ])->validate();

            $fields["dates_$i"] = true;
            $fields["date_$i"] = $date;
            $fields["from_time_$i"] = $request->input("from_time_$i");
            $fields["to_time_$i"] = $request->input("to_time_$i");
          }
        }
        $places = $request->places;
        if(!$places || !count($places)){
          return back()->withErrors(["error" => "يجب إختيار مكان واحد على الأقل."]);
        }
        $fields["places"] = $places;
        //validate in function
        $other_reservations = $this->getOtherReservationsWithFields(null, $fields, null, null, $type);
        if(isset($other_reservations["error"])){
          return back()->withErrors($other_reservations);
        }
        elseif(count($other_reservations) > 0){
            foreach($other_reservations as $or){
                if(count($or["reservations"]) > 0){
                    return back()->withErrors(["error" => "هناك نشاطات اخرى في نفس الوقت أو نفس المكان. قم بتعديل المعلومات وحاول مرة أخرى."]);
                }
            }
        }


        //all validated
        $reservation->save();
        $temporaryReservation = new TemporaryReservation();
        $i = 1;
        foreach($request->input("equipment_needed") as $en){
          if($en == null){
            continue;
          }
          switch($i){
            case 1:
              $temporaryReservation->equipment_needed_1 = $en;
              $i++;
              break;
            case 2:
              $temporaryReservation->equipment_needed_2 = $en;
              $i++;
              break;
            case 3:
              $temporaryReservation->equipment_needed_3 = $en;
              $i++;
          }

          if($i > 3){
            break;
          }
        }
        $temporaryReservation->reservation()->associate($reservation);
        $temporaryReservation->save();

        for($i = 0; $i < 3; $i++){
          if(isset($fields["dates_$i"])){
            $trd = new TemporaryReservationDate();
            $trd->date = $fields["date_$i"];
            $trd->from_time = $fields["from_time_$i"];
            $trd->to_time = $fields["to_time_$i"];
            $trd->temporaryReservation()->associate($temporaryReservation);
            $trd->save();
          }
        }

        foreach($places as $place){
          $trp = new TemporaryReservationPlace();
          $placesArr = explode("_", $place);
          $floor = Floor::find($placesArr[0]);
          if(!$floor || $floor->trashed()){
            return back()->withErrors(['floor' => __('المكان غير موجود')]);
          }
          $trp->floor()->associate($floor);
          if(count($placesArr) == 2){
            $room = Room::find($placesArr[1]);
            if(!$room || $room->trashed()){
              return back()->withErrors(['room' => __('المكان غير موجود')]);
            }
            $trp->room()->associate($room);
          }
          $trp->temporaryReservation()->associate($temporaryReservation);
          $trp->save();
        }
      }
      if($user->isAdmin()){
        $request->session()->flash('message', __("تم إضافة الحجز"));
        $request->session()->flash("message_class", "success");
        return redirect("/view-reservation/".$reservation->id);
      }
      else{
        $request->session()->flash('message', __("تم إرسال طلب إضافة الحجز"));
        $request->session()->flash("message_class", "success");
        $adminUsers = User::where("is_admin", "1")->get();
        $adminUsers->each(function($value) use($reservation) {
            Notification::send($value,
                new UserNotifications(__('طلب حجز جديد'),
                    "/reservation/".$reservation->id));
        });
        return redirect("/my-reservations");
      }
    }

    /**
     * show all reservations (for admin)
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function allReservations(Request $request){
      $reservations = Reservation::orderBy('date_created', "desc")->get();
      $manualReservations = ManualReservation::orderBy('date_created', 'desc')->get();
      $allReservations = $reservations->concat($manualReservations)->sortByDesc('date_created');
      $allReservations = paginate($allReservations, $this->pagination_nb)->setPath($request->path());
      $user = Auth::user();

      return view('new-reservations', ["reservations" => $allReservations, "user" => $user, "status" => 'all']);
    }
}
