<div class="iziModal pr-1 pl-1" id="pauseModal" tabindex="-1" role="dialog" aria-labelledby="pauseModalTitle"
     aria-hidden="true">
    <div class="modal-header">
        <h5 class="modal-title" id="pauseModalTitle">
            @if(isset($status) && $status === "edit")
                @lang("تعديل التوقيف المؤقت")
            @else 
                @lang("تأكيد التوقيف المؤقت")
            @endif
        </h5>
    </div>
    <div class="pt-3 pb-3">
        @if(isset($status))
            @if($status === "add")
                @lang("هل أنت متأكد من توقيف الحجز لوقت مؤقت؟")
            @endif
        @else
            @lang("هل أنت متأكد من توقيف الحجوزات لوقت مؤقت؟")
        @endif
    </div>
    <small class="mt-2">@lang('لتوقيف الحجز ليوم واحد، أدخل نفس التاريخ في الحقلين.')</small>
    <div class="pause-dates-container pt-3 mb-2">
        <div class="row">
            <div class="col-md-6 col-12">
                <label for="pause_from_date">@lang('توقيف من تاريخ')</label>
                @component("components.date-input", ["value" => isset($from_date) && $from_date ? $from_date : \Carbon\Carbon::now()->toDateString(),
                    "name" => "pause_from_date", "isDisabled" => false, "isRequired" => false])
                @endcomponent
            </div>
            <div class="col-md-6 col-12">
                <label for="pause_to_date">@lang('توقيف الى تاريخ')</label>
                @component("components.date-input", ["value" => isset($to_date) && $to_date ? $to_date : \Carbon\Carbon::now()->toDateString(),
                    "name" => "pause_to_date", "isDisabled" => false, "isRequired" => false])
                @endcomponent
            </div>
        </div>
    </div>
    @if(isset($class) && $class !== "App\ManualReservation")
        <div class="form-group mt-2">
            <label for="pausedReservationPlaces">أماكن إيقاف الحجز</label>
            <select multiple name="pausedReservationPlaces[]" class="form-control">
                @foreach($places as $place)
                    @php
                        $floor = $place->floor()->withTrashed()->first();
                        $room = $place->room()->withTrashed()->first();
                        if($floor->trashed() || ($room && $room->trashed())){
                            continue;
                        }
                        $value = $room ? $floor->id . "_" . $room->id : $floor->id;
                        $text = ($room ? ($room->name ? $room->name : "الغرفة ".$room->room_number)." - " : "").$floor->name;
                        $isSelected = false;
                        if(isset($selectedPlaces)){
                            $selectedPlace = $selectedPlaces->where('floor_id', $floor->id);
                            if($room){
                                $selectedPlace = $selectedPlace->where('room_id', $room->id);
                            }
                            $selectedPlace = collect($selectedPlace->all());
                            if($selectedPlace->count()){
                                $isSelected = true;
                            }
                        }
                    @endphp
                    <option value="{{$value}}" {{$isSelected ? 'selected' : ''}}>{{$text}}</option>
                @endforeach
            </select>
        </div>
    @elseif(!isset($class) && isset($floors) && isset($rooms))
        <select name="pausedReservationPlaces[]" class="form-control" multiple>
            @foreach ($floors as $floor)
                <option value="{{$floor->id}}" {{$floor->number_of_rooms > 0 ? 'disabled' :
              (old("pausedReservationPlaces") && array_search($floor->id, old("pausedReservationPlaces")) !== false ? "selected" : '')}}>
                    {{$floor->name}}
                </option>
                @if($floor->number_of_rooms > 0)
                    @foreach($floor->rooms()->get() as $room)
                        <option value="{{$floor->id}}_{{$room->id}}" {{old("pausedReservationPlaces") &&
                    array_search($floor->id."_".$room->id, old("pausedReservationPlaces")) !== false ?
                     "selected" : ''}}>
                            {{$room->name ? $room->name : __("الغرفة ").$room->room_number}}
                        </option>
                    @endforeach
                @endif
            @endforeach
        </select>
    @endif
    <div class="modal-footer">
        <button class="btn btn-success text-white {{isset($status) ? ($status === "edit" ? 'edit-pause-btn' : ($status === "add" ? 'pause-reservation-button' : 'mass-action')) : 'mass-action'}}" type="button" id="{{isset($status) ? ($status === "edit" ? 'editPausedReservation_'.$id : ($status === "add" ? 'pauseReservation_'.$id : 'massPause')) : 'massPause'}}">
            {{isset($status) && $status === "edit" ? __('تعديل') : __('نعم')}}
        </button>
        <button class="btn btn-secondary" type="button" data-izimodal-close="">
            {{isset($status) && $status === "edit" ? __('إلغاء التعديل') : __('لا')}}
        </button>
    </div>
</div>