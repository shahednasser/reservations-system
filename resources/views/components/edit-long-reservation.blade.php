<div class="row mb-5">
  <div class="col-lg-6 col-md-6 col-sm-12 col-12">
    <label for="from_date">{{__("تاريخ البداية")}}</label>
      @component('components.date-input', ["value" => old('from_date') ?: ($reservation ? \Carbon\Carbon::createFromFormat("Y-m-d H:i:s", $reservation->longReservation->from_date)->toDateString() : date("Y-m-d")),
                                          "name" => "from_date", "isDisabled" => false, "isRequired" => true])
      @endcomponent
  </div>
  <div class="col-lg-6 col-md-6 col-sm-12 col-12">
    <label for="to_date">{{__("تاريخ الانتهاء")}}</label>
      @component('components.date-input', ["value" => old('to_date') ?: ($reservation ? \Carbon\Carbon::createFromFormat("Y-m-d H:i:s", $reservation->longReservation->to_date)->toDateString() : date('Y-m-d')),
                                          "name" => "to_date", "isDisabled" => false, "isRequired" => true])
      @endcomponent
  </div>
</div>
<small>@lang("ضع علامة ✓ عند اليوم المطلوب فقط. إستخدم التوقيت بصيغة (24/24). إستعمل الأرقام باللغة الأجنبية.")</small>
<table class="table reservations-table table-bordered">
  <thead>
    <tr>
      <th scope="col">&#x2713;</th>
      <th scope="col">@lang('اليوم')</th>
      <th scope="col">@lang("من الساعة (24\\24)")</th>
      <th scope="col">@lang("الى الساعة (24\\24)")</th>
      <th scope="col">@lang("النشاط")</th>
      <th scope="col">@lang("المكان")</th>
    </tr>
  </thead>
  <tbody>
    @php
      $dates = $reservation ? $reservation->longReservation->longReservationDates()->get() : collect([]);
      $days = ["الإثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت", "الأحد"]
    @endphp
    @for($day = 0; $day < 7; $day++)
      <tr>
        @php
          $j = $day == 6 ? 0 : $day + 1;
          $date = $dates->where("day_of_week", $j)->first();
        @endphp
        <th scope="row">
          <input type="checkbox" name="day_{{$day}}" class="form-control"
            {{old("day_$day") ? "checked" : ($date ? 'checked' : '')}} />
        </th>
        <td>{{$days[$day]}}</td>
        <td>
          @component('components.time-input', ["name" => "from_time_".$day,
                      "value" => old("from_time_$day") ?: ($date ? format_time_without_seconds($date->from_time) : null),
                      "isDisabled" => $date || old("day_$day") ? false : true])
          @endcomponent
        </td>
        <td>
          @component('components.time-input', ["name" => "to_time_".$day,
                      "value" => old("to_time_$day") ?: ($date ? format_time_without_seconds($date->to_time) : null),
                      "isDisabled" => $date || old("day_$day") ? false : true])
          @endcomponent
        </td>
        <td>
          <input type="text" name="event_{{$day}}" value="{{old("event_$day") ?: ($date && $date->event ? $date->event : '')}}"
            class="form-control" {{!$date && !old("day_$day") ? 'disabled' : ''}} />
        </td>
        <td>
          <select name="place_{{$day}}[]" class="form-control" {{!$date && !old("day_$day") ? 'disabled' : ''}} multiple>
            @php
              $places = $date ? $date->longReservationPlaces()->get() : collect([]);
            @endphp
            @foreach ($floors as $floor)
              @php
                $floorSelected = $floor->number_of_rooms == 0 ? $places->where('floor_id', $floor->id)->first() : null;
              @endphp
              <option value="{{$floor->id}}" {{$floor->number_of_rooms > 0 ? 'disabled' :
              (old("place_".$day) && array_search($floor->id, old("place_".$day)) !== false ? "selected" :
              ($floorSelected ? 'selected' : ''))}}>
                {{$floor->name}}
              </option>
              @if($floor->number_of_rooms > 0)
                @foreach($floor->rooms()->get() as $room)
                  @php
                    $roomSelected = $places->where('floor_id', $floor->id)->where('room_id', $room->id)->first();
                  @endphp
                  <option value="{{$floor->id}}_{{$room->id}}" {{old("place_".$day) &&
                    array_search($floor->id."_".$room->id, old("place_".$day)) !== false ? "selected" :
                    ($roomSelected ? 'selected' : '')}}>
                    {{$room->name ? $room->name : __("الغرفة ").$room->room_number}}
                  </option>
                @endforeach
              @endif
            @endforeach
          </select>
        </td>
      </tr>
    @endfor
  </tbody>
</table>
