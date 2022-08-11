<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Floor;
use App\Room;
use App\ManualReservation;
use App\Equipment;
use App\HospitalityRequirment;
use App\ManualHospitalityRequirment;
use App\ManualPlace;
use App\ManualPlaceRequirment;
use App\ManualPlaceRequirmentsDate;
use App\ManualReligiousRequirment;
use App\ManualReservationEquipment;
use App\PlaceRequirment;
use App\ReligiousRequirment;
use App\LongReservation;
use App\TemporaryReservation;
use App\ManualReservationsDate;

use Validator;

class ManualReservationController extends Controller
{
  public function __construct(){
    $this->middleware('auth');
    $this->middleware('admin')->except('viewReservation');
  }

    /**
     * show add reservation form
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function adminAddReservation(){
    $user = Auth::user();

    $floors = Floor::all();
    $rooms = Room::all();
    $equipments = Equipment::all();
    $hospitalityRequirments = HospitalityRequirment::all();
    $manualPlaces = ManualPlace::all();
    $placeRequirments = PlaceRequirment::all();
    $religiousRequirments = ReligiousRequirment::all();
    return view("admin-add-reservation", ["user" => $user, "floors" => $floors, "rooms" => $rooms,
                                          "equipments" => $equipments, "hospitalityRequirments" => $hospitalityRequirments,
                                          "manualPlaces" => $manualPlaces, "placeRequirments" => $placeRequirments,
                                          "religiousRequirments" => $religiousRequirments]);
  }

  public function checkReservation(Request $request){
    $fields = $request->fields;
    $id = null;

    if(strpos($request->header('referer'), 'edit') !== false){
      $id = $request->id;
      if(!$id){
        return response()->json(["error" => __('المعلومات ناقصة')]);
      }
      $reservation = ManualReservation::find($id);
      if(!$reservation){
        return response()->json(["error" => __("المعلومات خاطئة")]);
      }
    }
    $other_reservations = $this->getOtherReservations($fields, $id);
    if(isset($other_reservations["error"])){
      return response()->json($other_reservations);
    }

    return response()->json(["reservations" => $other_reservations]);
  }

    /**
     * get conflicting reservations of a new reservation
     * @param $fields
     * @return array
     */
    private function getOtherReservations($fields, $id = null){
    $hasDays = false;
    $other_reservations = [];
    for($i = 0; $i < 3; $i++){
      if(isset($fields["dates_$i"])){
        if(!isset($fields["date_$i"]) || !isset($fields["from_time_$i"]) || !isset($fields["to_time_$i"])){
          return ["error" => "المعلومات ناقصة"];
        }
        $hasDays = true;
        $date = $fields["date_$i"];
        $validator = Validator::make(['date' => $date], [
          "date" => 'required|date'
        ]);
        if($validator->fails()){
          return ["error" => "التاريخ غير صحيح"];
        }
        $from_time = $fields["from_time_$i"];
        $to_time = $fields["to_time_$i"];
        if(!validateTime($from_time) || !validateTime($to_time)){
          return ["error" => "الوقت غير صحيح"];
        }
        if(!validateFromTime($from_time, $to_time)){
          return ["error" => "لا يمكن أن يكون وقت البداية بعد أو في نفس وقت النهاية للنشاط."];
        }
        $place = null;
        if(isset($fields["place_$i"])){
          $place = ManualPlace::find($fields["place_$i"]);
          if(!$place){
            return ["error" => "المكان غير صحيح."];
          }
        }
        else{
          $place = ManualPlace::all();
          if($place->count() == 1){
            $place = $place->first();
          }
          else{
            return ["error" => "المكان غير صحيح."];
          }
        }
        $fl = $place->floor()->withTrashed()->first();
        $rm = $place->room()->withTrashed()->first();
        $place = ["floor" => $fl, "room" => $rm];
        getOtherReservationsForManual($other_reservations, $i, $date, $id, $place, $from_time, $to_time);
      }
    }

    if(!$hasDays){
      return ["error" => "يجب أن تختار يوم واحد على الأقل"];
    }
    return $other_reservations;
  }


    /**
     * add new reservation
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function postAddAdminReservation(Request $request){
    Validator::make($request->all(), [
      'name' => 'required',
      'mobilePhone' => 'nullable|digits_between:8,20',
      'homePhone' => 'nullable|digits_between:8,20',
      'eventName' => 'nullable',
      'date_created' => 'required|date',
      "discount" => "nullable|numeric"
    ])->validate();

    $manualReservation = new ManualReservation([
      "full_name" => $request->name,
      "organization" => $request->organization,
      "mobile_phone" => $request->mobilePhone,
      "home_phone" => $request->homePhone,
      "event_name" => $request->eventName,
      "event_type" => $request->eventType,
      "date_created" => $request->date_created,
      "discount" => $request->discount ? $request->discount : 0
    ]);
    $mrds = [];

    //validate date/time
    $checked = 0;
    $fields = [];
    for($i = 0; $i < 3; $i++){
      if(null !== $request->input("dates_$i")){
        $date = $request->input("date_$i");
        $from_time = $request->input("from_time_$i");
        $to_time = $request->input("to_time_$i");
        $men = $request->input("men_$i");
        $women = $request->input("women_$i");

        Validator::make([
          "dates_$i" => $date,
          "from_time_$i" => $from_time,
          "to_time_$i" => $to_time
        ], [
          "dates_$i" => 'required|date',
          "from_time_$i" => 'required',
          "to_time_$i" => 'required'
        ])->validate();

        if(!validateTime($from_time)){
          return back()->withInput($request->input())->withErrors(["from_time_$i" => __('الوقت غير صحيح')]);
        }

        if(!validateTime($to_time)){
          return back()->withInput($request->input())->withErrors(["to_time_$i" => __('الوقت غير صحيح')]);
        }

        if(!validateFromTime($from_time, $to_time)){
          return back()->withInput($request->input())->withErrors(["from_time_$i" => __('وقت البداية لا يجب أن يكون في نفس وقت النهاية أو بعده.')]);
        }

        if($men === null && $women === null){
          return back()->withInput($request->input())->withErrors(["error" => __('يجب إختيار واحد على الأقل من النساء أو الرجال.')]);
        }
        $manualReservationDate = new ManualReservationsDate([
          "date" => $date,
          "from_time" => $from_time,
          "to_time" => $to_time,
          "for_women" => $women ? 1 : 0,
          "for_men" => $men ? 1 : 0
        ]);
        $mrds[$i] = $manualReservationDate;
        $checked++;
        $fields["dates_$i"] = true;
        $fields["date_$i"] = $date;
        $fields["from_time_$i"] = $from_time;
        $fields["to_time_$i"] = $to_time;
      }
    }

    $other_reservations = $this->getOtherReservations($fields);
    if(isset($other_reservations["error"])){
      return back()->withInput($request->input())->withErrors($other_reservations);
    }
    if(count($other_reservations) > 0){
      return back()->withInput($request->input())->withErrors(["error" => __('هذا الحجز يتعارض مع أوقات أخرى. الرجاء التأكد وإعادة المحاولة.')]);
    }

    if($checked == 0){
      return back()->withInput($request->input())->withErrors(["error" => __("يجب إختيار يوم واحد على الأقل.")]);
    }
    if($checked > 3){
      return back()->withInput($request->input())->withErrors(["error" => __("يجب إختيار 3 أيام أو أقل.")]);
    }

    //validate place
    $manualPlaces = PlaceRequirment::all();
    $manualPlacesReqs = [];
    $mprds = [];
    foreach($manualPlaces as $manualPlace){
      $nb = $request->input("place_requirment_".$manualPlace->id);
      if(null !== $nb){
        if($nb > $checked && $nb != 0){
          return back()->withInput($request->input())->withErrors(["error" => __('عدد أيام المستلزمات لا يجب أن يكون أكثر من عدد الأيام المختارة.')]);
        }
        $mpr = new ManualPlaceRequirment([
          "nb_days" => $nb
        ]);
        $mpr->placeRequirment()->associate($manualPlace);
        $manualPlacesReqs[] = $mpr;
        $dates = $request->input("place_requirment_dates_".$manualPlace->id);
        if($dates){
          $j = 0;
          for($i = 1; $i <= $nb; $i++){
            $date = isset($dates[$j]) ? $dates[$j] : null;
            if($date != null && $date > $nb){
              return back()->withInput($request->input())->withErrors(["place_requirment_dates_".$manualPlace->id => __("عدد التواريخ لا يجب أن يكون أكثر من عدد الأيام.")]);
            }
            if($date == null){
              $j  = $j + 1 % $nb;
              continue;
            }
            if(!array_key_exists($date, $mrds)){
              return back()->withInput($request->input())->withErrors(["place_requirment_dates_".$manualPlace->id => __("التاريخ المختار غير صحيح.")]);
            }
            $mprd = new ManualPlaceRequirmentsDate();
            $mprds[] = ["mprd" => $mprd, "date" => $mrds[$date], "mpr" => $mpr];
            $j  = $j + 1 % $nb;
          }
        }
      }
    }

    $hospitalityRequirments = HospitalityRequirment::all();
    $hrs = [];
    foreach($hospitalityRequirments as $hospitalityRequirment){
      $nb = $request->input("hospitality_requirment_nb_".$hospitalityRequirment->id);
      if(null !== $nb && $nb != 0){
        if($nb > $checked){
          return back()->withInput($request->input())->withErrors(["error" => __('عدد أيام المستلزمات لا يجب أن يكون أكثر من عدد الأيام المختارة.')]);
        }
        $mhr = new ManualHospitalityRequirment([
          "nb_days" => $nb
        ]);
        if(!$hospitalityRequirment->price){
          if(null === $request->input('requirmentSinglePrice_'.$hospitalityRequirment->id)){
            return back()->withInput($request->input())->withErrors(["error" => __('يحب تحديد السعر في مستلزمات الضيافة الإضافية.')]);
          }
          $mhr->additional_price = $request->input('requirmentSinglePrice_'.$hospitalityRequirment->id);
          $mhr->additional_name = $request->input('hospitality_requirment_additional_name_'.$hospitalityRequirment->id);
        }
        $mhr->hospitalityRequirment()->associate($hospitalityRequirment);
        $hrs[] = $mhr;
      }
    }


    $religiousRequirments = ReligiousRequirment::all();
    $mrrs = [];
    foreach($religiousRequirments as $religiousRequirment){
      $nb = $request->input("religious_requirment_nb_".$religiousRequirment->id);
      if(null !== $nb && $nb != 0){
        if($nb > $checked){
          return back()->withInput($request->input())->withErrors(["error" => __('عدد أيام المستلزمات لا يجب أن يكون أكثر من عدد الأيام المختارة.')]);
        }
        $mrr = new ManualReligiousRequirment([
          "nb_days" => $nb
        ]);
        $mrr->religiousRequirment()->associate($religiousRequirment);
        $mrrs[] = $mrr;
      }
    }

    //get and validate equipments
    $equipments = Equipment::all();
    $mres = [];
    foreach($equipments as $equipment){
      for($i = 1; $i <= $checked; $i++){
        $nb = $request->input("equipment_nb_".$i."_".$equipment->id);
        if(null !== $nb && $nb != 0){
          if($nb < 0){
            return back()->withInput($request->input())->withErrors(["equipment_nb_".$i."_".$equipment->id => __('يجب أن يكون عدد المستلزمات صفؤ أو أكثر.')]);
          }
          if($i > $checked){
            return back()->withInput($request->input())->withErrors(["equipment_nb_".$i."_".$equipment->id => __('لم تختار هذا اليوم')]);
          }
          $mre = new ManualReservationEquipment([
            "number" => $nb,
            "day_nb" => $i
          ]);
          $mre->equipment()->associate($equipment);
          $mres[] = $mre;
        }
      }
    }
    //all validated, add everything to db
    $place = ManualPlace::first(); // TODO: change this to check if there is more than one manual place
    $manualReservation->manualPlace()->associate($place);
    $manualReservation->save();

    foreach($mrds as $key => $mrd){
      $mrd->manualReservation()->associate($manualReservation);
      $mrd->save();
    }

    foreach($manualPlacesReqs as $mpr){
      $mpr->manualReservation()->associate($manualReservation);
      $mpr->save();
    }

    foreach($mprds as $mprdArr){
      $mprd = $mprdArr["mprd"];
      $date = $mprdArr["date"];
      $mpr = $mprdArr["mpr"];
      $mprd->manualReservationsDate()->associate($date);
      $mprd->manualPlaceRequirment()->associate($mpr);
      $mprd->save();
    }

    foreach($hrs as $hr){
      $hr->manualReservation()->associate($manualReservation);
      $hr->save();
    }

    foreach($mrrs as $mrr){
      $mrr->manualReservation()->associate($manualReservation);
      $mrr->save();
    }

    foreach($mres as $mre){
      $mre->manualReservation()->associate($manualReservation);
      $mre->save();
    }

    $request->session()->flash('message', __("تم إضافة الحجز"));
    $request->session()->flash("message_class", "success");

    return redirect('/view-admin-reservation/'.$manualReservation->id);
  }

    /**
     * view a reservation (for admin and user)
     * @param $id
     * @return mixed
     */
    public function viewReservation($id){
    $reservation = ManualReservation::find($id);
    if(!$reservation){
      abort(404);
    }
    $reservation->load(['manualHospitalityRequirments', 'manualPlace', 'manualReligiousRequirments',
                        'manualReservationEquipments', 'manualReservationsDates', 'manualReservationEquipments.equipment',
                        'manualHospitalityRequirments.hospitalityRequirment', 'manualReligiousRequirments.religiousRequirment',
                        'manualReservationsDates.manualPlaceRequirmentsDates.manualPlaceRequirment.placeRequirment']);
    $user = Auth::user();
    $hospitalityRequirment = HospitalityRequirment::all();
    $religiousRequirment = ReligiousRequirment::all();
    $placeRequirment = PlaceRequirment::all();
    $equipments = Equipment::all();

    return view('view-admin-reservation')->withUser($user)->withReservation($reservation)
            ->with('hospitalityRequirments', $hospitalityRequirment)
            ->with('religiousRequirments', $religiousRequirment)
            ->with('placeRequirments', $placeRequirment)
            ->withEquipments($equipments);

  }

    /**
     * delete a reservation
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function deleteReservation(Request $request, $id){
    $reservation = ManualReservation::find($id);
    if(!$reservation){
      abort(404);
    }
    $reservation->delete();

    $request->session()->flash('message', __("تم حذف الحجز"));
    $request->session()->flash("message_class", "success");
    return redirect("/all-reservations");
  }

    /**
     * show edit form of a reservation
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showEditReservation($id){
    $reservation = ManualReservation::find($id);
    if(!$reservation){
      abort(404);
    }
    $user = Auth::user();

    $floors = Floor::all();
    $rooms = Room::all();
    $equipments = Equipment::all();
    $hospitalityRequirments = HospitalityRequirment::all();
    $manualPlaces = ManualPlace::all();
    $placeRequirments = PlaceRequirment::all();
    $religiousRequirments = ReligiousRequirment::all();
    return view("admin-add-reservation", ["user" => $user, "floors" => $floors, "rooms" => $rooms,
                                          "equipments" => $equipments, "hospitalityRequirments" => $hospitalityRequirments,
                                          "manualPlaces" => $manualPlaces, "placeRequirments" => $placeRequirments,
                                          "religiousRequirments" => $religiousRequirments, "reservation" => $reservation]);
  }

  public function editReservation(Request $request, $id){
    $manualReservation = ManualReservation::find($id);
    if(!$manualReservation){
      abort(404);
    }
    Validator::make($request->all(), [
      'name' => 'required',
      'mobilePhone' => 'nullable|digits_between:8,20',
      'homePhone' => 'nullable|digits_between:8,20',
      'eventName' => 'nullable',
      'date_created' => 'required|date',
      "discount" => "nullable|numeric"
    ])->validate();

    $manualReservation->full_name = $request->name;
    $manualReservation->organization = $request->organization;
    $manualReservation->mobile_phone = $request->mobilePhone;
    $manualReservation->home_phone = $request->homePhone;
    $manualReservation->event_name = $request->eventName;
    $manualReservation->event_type = $request->eventType;
    $manualReservation->date_created = $request->date_created;
    $manualReservation->discount = $request->discount ? $request->discount : 0;

    $mrds = [];
    $oldMrds = $manualReservation->manualReservationsDates()->get();
    $arrMrds = $oldMrds->toArray();
    $deleteMrds = [];
    //validate date/time
    $checked = 0;
    $fields = [];
    for($i = 0; $i < 3; $i++){
      if(null !== $request->input("dates_$i")){
        $date = $request->input("date_$i");
        $from_time = $request->input("from_time_$i");
        $to_time = $request->input("to_time_$i");
        $men = $request->input("men_$i");
        $women = $request->input("women_$i");

        Validator::make([
          "dates_$i" => $date,
          "from_time_$i" => $from_time,
          "to_time_$i" => $to_time
        ], [
          "dates_$i" => 'required|date',
          "from_time_$i" => 'required',
          "to_time_$i" => 'required'
        ])->validate();

        if(!validateTime($from_time)){
          return back()->withInput($request->input())->withErrors(["from_time_$i" => __('الوقت غير صحيح')]);
        }

        if(!validateTime($to_time)){
          return back()->withInput($request->input())->withErrors(["to_time_$i" => __('الوقت غير صحيح')]);
        }

        if(!validateFromTime($from_time, $to_time)){
          return back()->withInput($request->input())->withErrors(["from_time_$i" => __('وقت البداية لا يجب أن يكون في نفس وقت النهاية أو بعده.')]);
        }

        if($men === null && $women === null){
          return back()->withInput($request->input())->withErrors(["error" => __('يجب إختيار واحد على الأقل من النساء أو الرجال.')]);
        }
        $manualReservationDate = null;
        if(isset($arrMrds[$i])){
          $manualReservationDate = $oldMrds->where("id", $arrMrds[$i]["id"])->first();
          $manualReservationDate->date = $date;
          $manualReservationDate->from_time = $from_time;
          $manualReservationDate->to_time = $to_time;
          $manualReservationDate->for_women = $women ? 1 : 0;
          $manualReservationDate->for_men = $men ? 1 : 0;
        }
        else{
          $manualReservationDate = new ManualReservationsDate([
            "date" => $date,
            "from_time" => $from_time,
            "to_time" => $to_time,
            "for_women" => $women ? 1 : 0,
            "for_men" => $men ? 1 : 0
          ]);
        }
        $mrds[$i] = $manualReservationDate;
        $checked++;
        $fields["dates_$i"] = true;
        $fields["date_$i"] = $date;
        $fields["from_time_$i"] = $from_time;
        $fields["to_time_$i"] = $to_time;
      }
    }

    $other_reservations = $this->getOtherReservations($fields, $id);
    if(isset($other_reservations["error"])){
      return back()->withInput($request->input())->withErrors($other_reservations);
    }
    if(count($other_reservations) > 0){
      return back()->withInput($request->input())->withErrors(["error" => __('هذا الحجز يتعارض مع أوقات أخرى. الرجاء التأكد وإعادة المحاولة.')]);
    }

    if($checked == 0){
      return back()->withInput($request->input())->withErrors(["error" => __("يجب إختيار يوم واحد على الأقل.")]);
    }
    if($checked > 3){
      return back()->withInput($request->input())->withErrors(["error" => __("يجب إختيار 3 أيام أو أقل.")]);
    }

    $diff = $oldMrds->diff($mrds);
    foreach($diff as $mrd){
      $deleteMrds[] = $mrd;
    }

    //validate place
    $manualPlaces = PlaceRequirment::all();
    $manualPlacesReqs = [];
    $mprds = [];
    $oldmprs = $manualReservation->manualPlaceRequirments()->get();
    $deletemprs = [];
    $deletemprds = [];
    foreach($manualPlaces as $manualPlace){
      $nb = $request->input("place_requirment_".$manualPlace->id);
      $mpr = $oldmprs->where("place_requirment_id", $manualPlace->id)->first();
      if(null !== $nb){
        if($nb > $checked && $nb != 0){
          return back()->withInput($request->input())->withErrors(["error" => __('عدد أيام المستلزمات لا يجب أن يكون أكثر من عدد الأيام المختارة.')]);
        }
        if($mpr){
          $mpr->nb_days = $nb;
        }
        else{
          $mpr = new ManualPlaceRequirment([
            "nb_days" => $nb
          ]);
          $mpr->placeRequirment()->associate($manualPlace);
        }
        $manualPlacesReqs[] = $mpr;
        $dates = $request->input("place_requirment_dates_".$manualPlace->id);
        if($mpr->exists){
          $oldmprds = $mpr->manualPlaceRequirmentsDates()->get();
          $deletemprds = array_merge($deletemprds, $oldmprds->all());
        }
        if($dates){
          $j = 0;
          $oldmprds = null;
          for($i = 1; $i <= $nb; $i++){
            $date = isset($dates[$j]) ? $dates[$j] : null;
            if($date != null && $date > $nb){
              return back()->withInput($request->input())->withErrors(["place_requirment_dates_".$manualPlace->id => __("عدد التواريخ لا يجب أن يكون أكثر من عدد الأيام.")]);
            }
            if($date == null){
              $j  = $j + 1 % $nb;
              continue;
            }
            if(!array_key_exists($date, $mrds)){
              return back()->withInput($request->input())->withErrors(["place_requirment_dates_".$manualPlace->id => __("التاريخ المختار غير صحيح.")]);
            }

            $mprd = null;
            $mprd = new ManualPlaceRequirmentsDate();
            $mprds[] = ["mprd" => $mprd, "date" => $mrds[$date], "mpr" => $mpr];
            $j  = $j + 1 % $nb;
          }
        }
      }
    }
    $deletemprs = $oldmprs->diff($manualPlacesReqs)->all();
    $hospitalityRequirments = HospitalityRequirment::all();
    $hrs = [];
    $oldMhrs = $manualReservation->manualHospitalityRequirments()->get();
    foreach($hospitalityRequirments as $hospitalityRequirment){
      $nb = $request->input("hospitality_requirment_nb_".$hospitalityRequirment->id);
      if(null !== $nb && $nb != 0){
        if($nb > $checked){
          return back()->withInput($request->input())->withErrors(["error" => __('عدد أيام المستلزمات لا يجب أن يكون أكثر من عدد الأيام المختارة.')]);
        }
        $mhr = $oldMhrs->where("hospitality_requirment_id", $hospitalityRequirment->id)->first();
        if($mhr){
          $mhr->nb_days = $nb;
        }
        else{
          $mhr = new ManualHospitalityRequirment([
            "nb_days" => $nb
          ]);
          $mhr->hospitalityRequirment()->associate($hospitalityRequirment);
        }

        $hrs[] = $mhr;
      }
    }

    $deleteMhrs = $oldMhrs->diff($hrs)->all();


    $religiousRequirments = ReligiousRequirment::all();
    $mrrs = [];
    $oldMrrs = $manualReservation->manualReligiousRequirments()->get();
    foreach($religiousRequirments as $religiousRequirment){
      $nb = $request->input("religious_requirment_nb_".$religiousRequirment->id);
      if(null !== $nb && $nb != 0){
        if($nb > $checked){
          return back()->withInput($request->input())->withErrors(["error" => __('عدد أيام المستلزمات لا يجب أن يكون أكثر من عدد الأيام المختارة.')]);
        }
        $mrr = $oldMrrs->where("religious_requirment_id", $religiousRequirment->id)->first();
        if($mrr){
          $mrr->nb_days = $nb;
        }
        else{
          $mrr = new ManualReligiousRequirment([
            "nb_days" => $nb
          ]);
          $mrr->religiousRequirment()->associate($religiousRequirment);
        }

        $mrrs[] = $mrr;
      }
    }

    $deleteMrrs = $oldMrrs->diff($mrrs);

    //get and validate equipments
    $equipments = Equipment::all();
    $mres = [];
    $oldMres = $manualReservation->manualReservationEquipments()->get();
    foreach($equipments as $equipment){
      for($i = 1; $i <= $checked; $i++){
        $nb = $request->input("equipment_nb_".$i."_".$equipment->id);
        if(null !== $nb && $nb != 0){
          if($nb < 0){
            return back()->withInput($request->input())->withErrors(["equipment_nb_".$i."_".$equipment->id => __('يجب أن يكون عدد المستلزمات صفؤ أو أكثر.')]);
          }
          if($i > $checked){
            return back()->withInput($request->input())->withErrors(["equipment_nb_".$i."_".$equipment->id => __('لم تختار هذا اليوم')]);
          }
          $mre = $oldMres->where("equipment_id", $equipment->id)->where("day_nb", $i)->first();
          if($mre){
            $mre->number = $nb;
          }
          else{
            $mre = new ManualReservationEquipment([
              "number" => $nb,
              "day_nb" => $i
            ]);
            $mre->equipment()->associate($equipment);
          }

          $mres[] = $mre;
        }
      }
    }
    $deleteMres = $oldMres->diff($mres)->all();
    //all validated, add everything to db
    // TODO: change this to check if there is more than one manual place
    $manualReservation->save();

    foreach($mrds as $key => $mrd){
      if(!$mrd->exists){
        $mrd->manualReservation()->associate($manualReservation);
      }
      $mrd->save();
    }

    foreach($deleteMrds as $mrd){
      $mrd->delete();
    }

    foreach($manualPlacesReqs as $mpr){
      if(!$mpr->exists){
        $mpr->manualReservation()->associate($manualReservation);
      }

      $mpr->save();
    }

    foreach($deletemprs as $mpr){
      $mpr->delete();
    }

    foreach($deletemprds as $mprd){
      $mprd->delete();
    }

    foreach($mprds as $mprdArr){
      $mprd = $mprdArr["mprd"];
      $date = $mprdArr["date"];
      $mpr = $mprdArr["mpr"];
      $mprd->manualReservationsDate()->associate($date);
      $mprd->manualPlaceRequirment()->associate($mpr);
      $mprd->save();
    }

    foreach($hrs as $hr){
      if(!$hr->exists){
        $hr->manualReservation()->associate($manualReservation);
      }
      $hr->save();
    }

    foreach($deleteMhrs as $mhr){
      $mhr->delete();
    }

    foreach($mrrs as $mrr){
      if($mrr->exists){
        $mrr->manualReservation()->associate($manualReservation);
      }
      $mrr->save();
    }

    foreach($deleteMrrs as $mrrs){
      $mrrs->delete();
    }

    foreach($mres as $mre){
      if(!$mre->exists){
        $mre->manualReservation()->associate($manualReservation);
      }
      $mre->save();
    }

    foreach($deleteMres as $mre){
      $mre->delete();
    }

    $request->session()->flash('message', __("تم تعديل الحجز"));
    $request->session()->flash("message_class", "success");
    return redirect('/view-admin-reservation/'.$manualReservation->id);
  }
}
