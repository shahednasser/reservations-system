<table class="table reservations-table table-bordered">
  <thead class="thead-dark">
    <tr>
      <th class="w-15">
        @lang("تاريخ إقامته")
      </th>
    </tr>
  </thead>
  <tbody>
    @php
      $dates = [];
      $places = collect([]);
      if($reservation){
        $dates = $reservation->temporaryReservation->temporaryReservationDates()->get()->toArray();
        $places = $reservation->temporaryReservation->temporaryReservationPlaces()->get();
      }
    @endphp
    @for($i = 0; $i < 3; $i++)
      @php
        $hasDate = $i < count($dates);
      @endphp
      <tr>
        <td class="text-center align-middle">
          <input type="checkbox" name="dates_{{$i}}"
            {{old("dates_$i") ? "checked" : ($hasDate ? 'checked' : '')}} />
        </td>
        <td>
          <label for="date_{{$i}}">@lang("التاريخ")</label>
            @component('components.date-input', ["value" => old("date_$i") ?: ($hasDate ? $dates[$i]["date"] : ''),
                                          "name" => "date_$i", "isDisabled" => !$hasDate && !old("date_$i"), "isRequired" => false])
            @endcomponent
        </td>
        <td>
          <label for="from_time_{{$i}}">@lang("من الساعة")</label>
          @component('components.time-input', ['name' => 'from_time_'.$i,
                      'value' => old("from_time_$i") ?: ($hasDate ? format_time_without_seconds($dates[$i]['from_time']) :
                        null), 'isDisabled' => !$hasDate && !old("from_time_$i")])
          @endcomponent
        </td>
        <td>
          <label for="to_time_{{$i}}">@lang("حتى الساعة")</label>
          @component('components.time-input', ['name' => 'to_time_'.$i,
                      'value' => old("to_time_$i") ?: ($hasDate ? format_time_without_seconds($dates[$i]['to_time']) :
                        null), 'isDisabled' => !$hasDate && !old("to_time_$i")])
          @endcomponent
        </td>
      </tr>
    @endfor
  </tbody>
</table>
<div class="w-md-15 w-50 bg-dark places-title text-white">
  @lang("الاماكن")
</div>
<div>
  @lang("يجب إختيار مكان واحد على الأقل")
</div>
<select name="places[]" multiple class="form-control">
  @foreach ($floors as $floor)
    @php
      $place = $places->where('floor_id', $floor->id);
      if($place){
        if($floor->number_of_rooms > 0){
          $place = collect($place->all());
        }
        else {
          $place = $place->first();
        }
      }
    @endphp
    <option value="{{$floor->id}}" {{$floor->number_of_rooms > 0 ? 'disabled' :
    (old('places') && array_search($floor->id, old('places')) !== false ? 'selected' :
    ($place && $place->floor && $place->floor->id === $floor->id ? 'selected' : ''))}}>
      {{$floor->name}}
    </option>
    @if($floor->number_of_rooms > 0)
      @foreach($floor->rooms()->get() as $room)
        @php
          $rplace = null;
          if($place){
            $rplace = $place->where('room_id', $room->id)->first();
          }
        @endphp
        <option value="{{$floor->id}}_{{$room->id}}" {{old('places') &&
          array_search($floor->id."_".$room->id, old('places')) !== false ? 'selected' : ($rplace && $rplace->room &&
        $rplace->room->id === $room->id ? 'selected' : '')}}>
          {{$room->name ? $room->name : __("الغرفة ").$room->room_number}}
        </option>
      @endforeach
    @endif
  @endforeach
</select>
