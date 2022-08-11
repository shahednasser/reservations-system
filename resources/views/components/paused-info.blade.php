<div class='alert alert-info'>
    @lang("هذا الطلب متوقف من") {{format_date($pausedReservation->from_date)}} @lang("إلى")
    {{format_date($pausedReservation->to_date)}}
    @php
        $places = $pausedReservation->pausedReservationPlaces()->get();
    @endphp
    @if($places->count())
        @lang("في الأماكن التالية")
        <ul>
            @foreach($places as $place)
                <li>
                    @php
                        $floor = $place->floor()->withTrashed()->first();
                    @endphp
                    @if($floor)
                        {{$floor->name}}
                        @php
                            $room = $place->room()->withTrashed()->first();
                        @endphp
                        @if($room)
                            {{$room->name ? " - ".$room->name : __(" الغرفة ").$room->room_number}}
                            @if($room->trashed())
                                (@lang('محذوف'))
                            @endif
                        @endif
                        @if($floor->trashed())
                            (@lang('محذوف'))
                        @endif
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>