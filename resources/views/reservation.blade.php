@extends('master.authenticated', ["user" => $user])
@php
  $title = "طلب إقامة نشاط".($reservation->longReservation ? ' دوري مستمر' : '');
@endphp
@section('title', $title)

@push("stylesheets")
  <link href="/css/reservation.css?v=8" rel="stylesheet" />
  <link rel="stylesheet" href="/css/iziModal.min.css">
@endpush

@section('contents')
  @component('components.full-card')
    @slot('cardTitle')
      <div class="text-center">@lang($title)</div>
    @endslot
    @if($error)
      <div class="alert alert-danger">{{$error}}</div>
    @endif
    @if($reservation->isEditRequest)
      <div class="alert alert-info">
        @lang("طلب تعديل للنشاط")
        <a href="/view-reservation/{{$reservation->isEditRequest->reservation->id}}">
          {{$reservation->isEditRequest->reservation->event_name}}
        </a>
      </div>
    @elseif($reservation->deleteRequest)
      <div class="alert alert-info">
        @lang("طلب إلغاء للنشاط")
        <a href="/approve-delete-reservation/{{$reservation->id}}" />
          {{$reservation->event_name}}
        </a>
      </div>
    @endif
    @if($reservation->message || ($reservation->isEditRequest && $reservation->isEditRequest->reservation && $reservation->isEditRequest->reservation->message))
        <div class="alert alert-primary">
            <h5>@lang('شروط الحجز'):</h5>
            <p>
                {{$reservation->message ?: $reservation->isEditRequest->reservation->message}}
            </p>
        </div>
    @endif
    <div class="reservation-details">
      @php
        $count = 0;
        foreach($other_reservations as $or){
          $count += (isset($or["reservations"]) ? count($or["reservations"]) : 0)  +
                    (isset($or["manual_reservations"]) ? count($or["manual_reservations"]) : 0);
        }
        $days = ["الإثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت", "الأحد"];
      @endphp
      @if($reservation->longReservation) {{-- long reservation --}}
        <div class="row">
          <span class="col-lg-6 col-md-6 col-sm-12 col-12"><strong>@lang("الى: مركز ومسجد الحسن")</strong></span>
          <span class="col-lg-6 col-md-6 col-sm-12 col-12"><strong>@lang("تاريخ الطلب"):</strong> {{format_date($reservation->date_created)}}</span>
        </div>
        @php
            $resUser = $reservation->user()->withTrashed()->first();
        @endphp
        <div class="row">
          <span class="col-lg-4 col-md-4 col-sm-12 col-12"><strong>@lang("من (مقدم الطلب)"):</strong>
              {{$resUser->name}} ({{$resUser->username}})
          </span>
          <span class="col-lg-4 col-md-4 col-sm-12 col-12"><strong>@lang("صفته"):</strong>
              {{$resUser->position}}
          </span>
          <span class="col-lg-4 col-md-4 col-sm-12 col-12"><strong>@lang("لجنة"):</strong> {{$reservation->committee}}</span>
        </div>
        <div class='row'>
          <div class='col-12'>
            <strong>@lang("نرجو الموافقة على إقامة النشاط دوري مستمر ضمن حرم مركز ومسجد الحسن وفق المعلومات التالية"):</strong>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <strong>@lang('نوع النشاط'):</strong> {{$reservation->event_name}}
          </div>
        </div>
        <div class="row">
          <div class="col-lg-6 col-md-6 col-sm-12 col-12">
            <strong>@lang("تاريخ البداية"):</strong> {{format_date($reservation->longReservation->from_date)}}
          </div>
          <div class="col-lg-6 col-md-6 col-sm-12 col-12">
            <strong>@lang("تاريخ الانتهاء"):</strong> {{format_date($reservation->longReservation->to_date)}}
          </div>
        </div>
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
              $dates = $reservation->longReservation->longReservationDates()->get();
            @endphp
            @for($day = 0; $day < 7; $day++)
              <tr>
                @php
                  $j = $day == 6 ? 0 : $day + 1;
                  $date = $dates->where("day_of_week", $j)->first();
                @endphp
                <th scope="row">{{$date ? html_entity_decode('&#x2713;') : ''}}</th>
                <td>{{$days[$day]}}</td>
                <td>{{$date ? format_time_without_seconds($date->from_time) : ''}}</td>
                <td>{{$date ? format_time_without_seconds($date->to_time) : ''}}</td>
                <td>{{$date && $date->event ? $date->event : ''}}</td>
                <td>
                    @php
                        $places = $date ? $date->longReservationPlaces()->get() : collect([]);
                        $first = true;
                    @endphp
                    @foreach($places as $place)
                        @php
                            $floor = $place->floor()->withTrashed()->first();
                            $room = $place->room()->withTrashed()->first();
                        @endphp
                        @if(!$first)
                            <br />
                        @else
                            @php
                                $first = false;
                            @endphp
                        @endif
                        @if($room)
                            {{$room->name ? $room->name : "الغرفة ".$room->room_number}}
                            @if($room->trashed())
                                (@lang('محذوف'))
                            @endif
                            -
                        @endif
                        {{$floor->name}}
                        @if($floor->trashed())
                            (@lang('محذوف'))
                        @endif
                    @endforeach
                </td>
              </tr>
            @endfor
          </tbody>
        </table>
        <div class="row">
          <div class="col-12">
            <strong>@lang("ملاحظات"):</strong> {{$reservation->notes}}
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <strong>@lang("الأساتذة المشرفون"):</strong> {{$reservation->supervisors}}
          </div>
        </div>
        <hr />
        <div class="row">
          <div class="col-12">
            <h3><strong>@lang("النشاطات التى تتعارض مع التواقيت/الأماكن")</strong></h3>
          </div>
        </div>
        @if(!$count)
          <div class="row">
            <div class="col-12">
              @lang("لا يوجد اي نشاطات تتعارض مع التواقيت او الأماكن المذكورة.")
            </div>
          </div>
        @else
          <ul>
          @foreach($other_reservations as $or)
            @foreach($or["reservations"] as $key => $res)
              <li>
                @php
                  $is_long = get_class($res) == "App\LongReservation";
                @endphp
                  @lang("أسم النشاط"): <a href="/view-reservation/{{$res->reservation->id}}">{{$res->reservation->event_name}}</a><br />
                @if($is_long)
                  <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                      @lang("من"): {{format_date($res->from_date)}}
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                      @lang("الى"): {{format_date($res->to_date)}}
                    </div>
                  </div>
                  <table class="table table-hover table-bordered">
                    <thead>
                      <tr>
                        <th>@lang("اليوم")</th>
                        <th>@lang("يتعارض مع")</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($res->longReservationDates()->get() as $lrd)
                        @php
                        $dow = $dates->reject(function($value) use ($lrd){
                            return $value->day_of_week != $lrd->day_of_week ||
                                    !timeBetween($lrd->from_time, $value->from_time, $lrd->to_time, $value->to_time);
                        });
                         if(!$dow->count()){
                            continue;
                         }
                         $d = $dow->first();
                        @endphp
                        <tr>
                          <td>
                            {{$days[$lrd->day_of_week == 0 ? count($days) - 1 : $lrd->day_of_week - 1]}} {{format_time_without_seconds($lrd->from_time)}}
                            - {{format_time_without_seconds($lrd->to_time)}}
                              @php
                                  $places = $lrd ? $lrd->longReservationPlaces()->get() : collect([]);
                              @endphp
                              @foreach($places as $place)
                                  @php
                                      $floor = $place->floor()->withTrashed()->first();
                                      $room = $place->room()->withTrashed()->first();
                                  @endphp
                                  <br />
                                  @if($room)
                                      {{$room->name ? $room->name : "الغرفة ".$room->room_number}}
                                      @if($room->trashed())
                                          (@lang('محذوف'))
                                      @endif
                                      -
                                  @endif
                                  {{$floor->name}}
                                  @if($floor->trashed())
                                      (@lang('محذوف'))
                                  @endif
                              @endforeach
                          </td>
                          <td>
                            {{$days[$d->day_of_week == 0 ? count($days) - 1 : $d->day_of_week - 1]}} {{format_time_without_seconds($d->from_time)}}
                            - {{format_time_without_seconds($d->to_time)}}
                              @php
                                  $places = $d ? $d->longReservationPlaces()->get() : collect([]);
                              @endphp
                              @foreach($places as $place)
                                  @php
                                      $floor = $place->floor()->withTrashed()->first();
                                      $room = $place->room()->withTrashed()->first();
                                  @endphp
                                  <br />
                                  @if($room)
                                      {{$room->name ? $room->name : "الغرفة ".$room->room_number}}
                                      @if($room->trashed())
                                          (@lang('محذوف'))
                                      @endif
                                      -
                                  @endif
                                  {{$floor->name}}
                                  @if($floor->trashed())
                                      (@lang('محذوف'))
                                  @endif
                              @endforeach
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                @else
                  @lang("الاماكن"):
                  <ul>
                    @foreach($res->temporaryReservationPlaces()->get() as $trp)
                      <li>
                        @php
                          $floor = $trp->floor()->withTrashed()->first();
                          $room = $trp->room()->withTrashed()->first();
                        @endphp
                        @if($floor)
                          {{$floor->name}}
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
                  <table class="table table-hover table-bordered">
                    <thead>
                      <tr>
                        <th>@lang("اليوم")</th>
                        <th>@lang("يتعارض مع")</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($res->temporaryReservationDates()->get() as $trd)
                        @php
                          $dow = collect($dates->where("day_of_week", getDay($trd->date))->all());
                          $d = $dow->where("from_time", ">=", $trd->from_time)
                                    ->where("from_time", "<", $trd->to_time)->first();
                          if(!$d){
                            $d = $dow->where("to_time", ">", $trd->from_time)
                                      ->where("to_time", "<=", $trd->to_time)->first();
                            if(!$d){
                              $d = $dow->where("from_time", "<", $trd->from_time)
                                        ->where("to_time", ">", $trd->to_time)
                                        ->first();
                              if(!$d){
                                continue;
                              }
                            }
                          }
                        @endphp
                        <tr>
                          <td>
                            {{$days[getDay($trd->date) == 0 ? count($days) - 1 : getDay($trd->date) - 1]}} {{format_time_without_seconds($trd->from_time)}}
                            - {{format_time_without_seconds($trd->to_time)}}
                          </td>
                          <td>
                            {{$days[getDay($d->date) == 0 ? count($days) - 1 : getDay($d->date) - 1]}} {{format_time_without_seconds($d->from_time)}}
                            - {{format_time_without_seconds($d->to_time)}}
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                @endif
              </li>
            @endforeach
            @if(isset($or["manual_reservations"]))
              @foreach($or["manual_reservations"] as $key => $res)
                <li><a href="/view-admin-reservation/{{$res->id}}">{{$res->event_type}} - @lang("حجز قاعة")</a>
                  <table class="table table-hover table-bordered">
                    <thead>
                      <tr>
                        <th>@lang("اليوم")</th>
                        <th>@lang("يتعارض مع")</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($res->manualReservationsDates()->get() as $mrd)
                        @php
                          $dow = collect($dates->where("day_of_week", getDay($mrd->date))->all());
                          $d = $dow->where("from_time", ">=", $mrd->from_time)
                                    ->where("from_time", "<", $mrd->to_time)->first();
                          if(!$d){
                            $d = $dow->where("to_time", ">", $mrd->from_time)
                                      ->where("to_time", "<=", $mrd->to_time)->first();
                            if(!$d){
                              $d = $dow->where("from_time", "<", $mrd->from_time)
                                        ->where("to_time", ">", $mrd->to_time)
                                        ->first();
                              if(!$d){
                                continue;
                              }
                            }
                          }
                        @endphp
                        <tr>
                          <td>
                            {{$days[getDay($mrd->date) == 0 ? count($days) - 1 : getDay($mrd->date) - 1]}} {{format_time_without_seconds($mrd->from_time)}}
                            - {{format_time_without_seconds($mrd->to_time)}}
                          </td>
                          <td>
                            {{$days[$d->day_of_week == 0 ? count($days) - 1 : $d->day_of_week - 1]}} {{format_time_without_seconds($d->from_time)}}
                            - {{format_time_without_seconds($d->to_time)}}
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </li>
              @endforeach
            @endif
          @endforeach
          </ul>
        @endif
      @else
        <div class="row">
          <div class="col-md-6 col-12"><strong>@lang("تاريخ تقديم الطلب"): </strong>
            {{format_date($reservation->date_created)}}
          </div>
          <div class="col-md-6 col-12">
            <strong>
              @lang("مقدم الطلب"):
            </strong>
              @php
                $resUser = $reservation->user()->withTrashed()->first();
              @endphp
            {{$resUser->name}} ({{$resUser->username}})
          </div>
        </div>
        <div class="row">
          <span class="col-12"><strong>@lang("من"):</strong> {{$reservation->committee}}</span>
        </div>
        <div class="row">
          <span class="col-12"><strong>@lang("الى: مركز ومسجد الحسن")</strong></span>
        </div>
        <div class="row">
          <span class="col-12">@lang("نرجو الموافقة على اقامة النشاط التالي"):</span>
        </div>
        <ol>
          <li><strong>@lang("عنوان النشاط"):</strong> {{$reservation->event_name}}</li>
          <li>
            <div class="row">
              <div class="col-lg-3 col-md-3 col-sm-12 col-12"><strong>@lang("تاريخ اقامته"):</strong></div>
              @php
                $dates = $reservation->temporaryReservation->temporaryReservationDates()->get()->toArray();
                $i = 1;
              @endphp
              @foreach($dates as $date)
                @if($i != 1)
                  <div class="col-lg-3 col-md-3 col-sm-12 col-12"></div>
                @else
                  @php
                    $i = 2
                  @endphp
                @endif
                <div class="col-lg-3 col-md-3 col-sm-12 col-12">- {{format_date($date['date'])}}</div>
                <div class="col-lg-3 col-md-3 col-sm-12 col-12">@lang("من الساعة"):
                  {{format_time_without_seconds($date['from_time'])}}</div>
                  <div class="col-lg-3 col-md-3 col-sm-12 col-12">@lang("حتى الساعة"):
                    {{format_time_without_seconds($date['to_time'])}}</div>
              @endforeach
            </div>
          </li>
          <li><strong>@lang("مكان النشاط"):</strong>
            @php
              $places = $reservation->temporaryReservation->temporaryReservationPlaces()->get();
            @endphp
            @foreach($places as $place)
              <div class="row">
                <div class="col-12">
                  -
                  @php
                    $floor = $place->floor()->withTrashed()->first();
                    $room = $place->room()->withTrashed()->first();
                  @endphp
                  @if($floor)
                    {{$floor->name}}
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
                </div>
              </div>
            @endforeach
          </li>
          <li>
            <strong>@lang("المشرفون أثناء النشاط"):</strong> {{$reservation->supervisors ? $reservation->supervisors : ''}}
          </li>
          <li>
            <strong>@lang("المستلزمات المطلوبة"): </strong>
            @if($reservation->temporaryReservation->equipment_needed_1)
              <div class="row">
                <div class="col-12">
                  - {{$reservation->temporaryReservation->equipment_needed_1}}
                </div>
              </div>
            @endif
            @if($reservation->temporaryReservation->equipment_needed_2)
              <div class="row">
                <div class="col-12">
                  - {{$reservation->temporaryReservation->equipment_needed_2}}
                </div>
              </div>
            @endif
            @if($reservation->temporaryReservation->equipment_needed_3)
              <div class="row">
                <div class="col-12">
                  - {{$reservation->temporaryReservation->equipment_needed_3}}
                </div>
              </div>
            @endif
          </li>
          @if($reservation->notes)
            <li>
              <strong>@lang("ملاحظات إضافية"):</strong>
              <div class="row">
                <div class="col-12">
                  {{$reservation->notes}}
                </div>
              </div>
            </li>
          @endif
        </ol>
        <hr />
        <div class="row">
          <div class="col-12">
            <h3><strong>@lang("النشاطات التى تتعارض مع التواقيت/الأماكن")</strong></h3>
          </div>
        </div>
        @if(!$count)
          <div class="row">
            <div class="col-12">
              @lang("لا يوجد اي نشاطات تتعارض مع التواقيت او الأماكن المذكورة.")
            </div>
          </div>
        @else
          <ul>
            @php
              $dates = $reservation->temporaryReservation->temporaryReservationDates()->get();
            @endphp
            @foreach($other_reservations as $or)
              @foreach($or["reservations"] as $key => $res)
                <li>
                  @php
                    $is_long = get_class($res) == "App\LongReservation";
                  @endphp
                  @lang("أسم النشاط"): <a href="/view-reservation/{{$res->reservation->id}}">
                    {{$res->reservation->event_name}}
                    </a><br />
                      @if($is_long)
                        <div class="row">
                          <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                            @lang("من"): {{format_date($res->from_date)}}
                          </div>
                          <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                            @lang("الى"): {{format_date($res->to_date)}}
                          </div>
                        </div>
                        <table class="table table-hover table-bordered">
                          <thead>
                            <tr>
                              <th>@lang("اليوم")</th>
                              <th>@lang("يتعارض مع")</th>
                            </tr>
                          </thead>
                          <tbody>
                          @foreach($res->longReservationDates()->get() as $lrd)
                            @php
                              $d = $dates->reject(function($value, $key) use($lrd){
                                return getDay($value->date) != $lrd->day_of_week ||
                                      !timeBetween($lrd->from_time, $value->from_time, $lrd->to_time, $value->to_time);
                              });
                              if(!$d->count()){
                                continue;
                              }
                            @endphp
                            <tr>
                              <td>
                                {{$days[$lrd->day_of_week == 0 ? count($days) - 1 : $lrd->day_of_week - 1]}} {{format_time_without_seconds($lrd->from_time)}}
                                - {{format_time_without_seconds($lrd->to_time)}}
                              @php
                                  $places = $lrd ? $lrd->longReservationPlaces()->get() : collect([]);
                              @endphp
                              @foreach($places as $place)
                                  @php
                                      $floor = $place->floor()->withTrashed()->first();
                                      $room = $place->room()->withTrashed()->first();
                                  @endphp
                                  <br />
                                  @if($room)
                                      {{$room->name ? $room->name : "الغرفة ".$room->room_number}}
                                      @if($room->trashed())
                                          (@lang('محذوف'))
                                      @endif
                                      -
                                  @endif
                                  {{$floor->name}}
                                  @if($floor->trashed())
                                      (@lang('محذوف'))
                                  @endif
                              @endforeach
                              </td>
                              <td>
                                @foreach($d as $ds)
                                  {{$days[getDay($ds->date) == 0 ? count($days) - 1 : getDay($ds->date) - 1]}} {{format_time_without_seconds($ds->from_time)}}
                                  - {{format_time_without_seconds($ds->to_time)}}
                                @endforeach
                              </td>
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    @else
                      @lang("الاماكن"):
                      <ul>
                        @foreach($res->temporaryReservationPlaces()->get() as $trp)
                          <li>
                            @php
                              $floor = $trp->floor()->withTrashed()->first();
                              $room = $trp->room()->withTrashed()->first();
                            @endphp
                            @if($floor)
                              {{$floor->name}}
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
                      <table class="table table-hover table-bordered">
                        <thead>
                          <tr>
                            <th>@lang("اليوم")</th>
                            <th>@lang("يتعارض مع")</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($res->temporaryReservationDates()->get() as $trd)
                            @php
                              $d = $dates->reject(function($value, $key) use($trd){
                                return $value->date != $trd->date ||
                                        !(($value->from_time >= $trd->from_time &&
                                        $value->from_time < $trd->to_time) ||
                                        ($value->to_time > $trd->from_time &&
                                        $value->to_time <= $trd->to_time) ||
                                        ($value->from_time < $trd->from_time &&
                                          $value->to_time > $trd->to_time));
                              });
                              if(!$d->count()){
                                continue;
                              }
                            @endphp
                            <tr>
                              <td>
                              {{$days[getDay($trd->date) == 0 ? count($days) - 1 : getDay($trd->date) - 1]}} {{format_time_without_seconds($trd->from_time)}}
                              - {{format_time_without_seconds($trd->to_time)}}
                              </td>
                              @foreach($d as $ds)
                                <td>
                                {{$days[getDay($ds->date) == 0 ? count($days) - 1 : getDay($ds->date) - 1]}} {{format_time_without_seconds($ds->from_time)}}
                                - {{format_time_without_seconds($ds->to_time)}}
                                </td>
                              @endforeach
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  @endif
                </li>
              @endforeach
              @if(isset($or["manual_reservations"]))
                @foreach($or["manual_reservations"] as $key => $res)
                    <li><a href="/view-admin-reservation/{{$res->id}}">{{$res->event_type}} - @lang("حجز قاعة")</a>
                      <table class="table table-hover table-bordered">
                        <thead>
                          <tr>
                            <th>@lang("اليوم")</th>
                            <th>@lang("يتعارض مع")</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($res->manualReservationsDates()->get() as $mrd)
                            @php
                              $d = $dates->reject(function($value) use($mrd){
                                return $value->date != $mrd->date ||
                                        !(($value->from_time >= $mrd->from_time &&
                                        $value->from_time < $mrd->to_time) ||
                                        ($value->to_time > $mrd->from_time &&
                                        $value->to_time <= $mrd->to_time) ||
                                        ($value->from_time < $mrd->from_time &&
                                          $value->to_time > $mrd->to_time));
                              });
                              if(!$d->count()){
                                continue;
                              }
                            @endphp
                            <tr>
                              <td>
                              {{getDay($mrd->date) == 0 ? count($days) - 1 : getDay($mrd->date) - 1}} {{format_time_without_seconds($mrd->from_time)}}
                              - {{format_time_without_seconds($mrd->to_time)}}
                              </td>
                              @foreach($d as $ds)
                                <td>
                                {{getDay($ds->date) == 0 ? count($days) - 1 : getDay($ds->date) - 1}} {{format_time_without_seconds($ds->from_time)}}
                                - {{format_time_without_seconds($ds->to_time)}}
                                </td>
                              @endforeach
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </li>
                @endforeach
              @endif
            @endforeach
          </ul>
        @endif
      @endif
      <div class='row'>
        <div class="col-12 text-left">
          @if($reservation->deleteRequest)
            <a role="button" class="btn btn-success"
              href="/delete-reservation/{{$reservation->id}}">@lang("الموافقة")</a>
          @else
            @if(!$count)
              <button type="button" class="btn btn-success" data-izimodal-open="#approveModal"
                data-izimodal-transitionin="fadeInDown">@lang("الموافقة")</button>
            @else
              <button type="button" class="btn btn-success" data-izimodal-open="#editModal"
              data-izimodal-transitionin="fadeInDown">
                @lang("تعديل")</button>
            @endif
          @endif
          <button class="btn btn-danger" type="button" data-izimodal-open="#rejectModal"
          data-izimodal-transitionin="fadeInDown">@lang("رفض الطلب")</button>
        </div>
      </div>
    </div>
  @endcomponent
@endsection

@section("modals")
    <div class="iziModal pr-1 pl-1" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalTitle"
         aria-hidden="true">
        <div class="modal-header">
            <h5 class="modal-title" id="approveModalTitle">
                @lang('الموافقة على الحجز')
            </h5>
        </div>
        <form action="/approve-reservation/{{$reservation->id}}" method="post">
            @csrf
            <div class="form-group">
                <label>@lang('يمكنك إضافة شروط لصاحب الحجز')</label>
                <textarea name="approve_message" class="form-control" cols="50" rows="7"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" type="submit">
                    @lang('الموافقة')
                </button>
                <button class="btn btn-secondary" type="button" data-izimodal-close="">@lang("إلغاء")</button>
            </div>
        </form>
    </div>
  <div class="iziModal pr-1 pl-1" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalTitle"
    aria-hidden="true">
    <div class="modal-header">
      <h5 class="modal-title" id="rejectModalTitle">@lang("تأكيد الرفض")</h5>
    </div>
    @if($reservation->deleteRequest)
      <div class="pt-3 pb-3">
        @lang("هل أنت متأكد من رفض الطلب؟")
      </div>
      <div class="modal-footer">
        <a class="btn btn-danger" role="button" href="/reject-request/{{$reservation->id}}">@lang("رفض الطلب")</a>
        <button class="btn btn-secondary" type="button" data-dismiss="modal">@lang("إلغاء")</button>
      </div>
    @else
      @if(!$reservation->isEditRequest)
      <form action="/reject-reservation/{{$reservation->id}}" method="post">
        @csrf
        <div class="pt-3 pb-3">
          @lang("يمكنك إرسال سبب للرفض")
          <textarea class="form-control" name="rejection_message" placeholder='{{__("مثلا: الرجاء إختيار مكان اّخر")}}'></textarea>
        </div>
        <div class="modal-footer">
          <button class="btn btn-danger" type="submit">@lang("رفض الطلب")</button>
          <button class="btn btn-secondary" type="button" data-izimodal-close="">@lang("إلغاء")</button>
        </div>
      </form>
      @else
        <div class="pt-3 pb-3">
          @lang("هل أنت متأكد من رفض الطلب؟")
        </div>
        <div class="modal-footer">
          <a class="btn btn-danger" role="button" href="/reject-request/{{$reservation->id}}">@lang("رفض الطلب")</a>
          <button class="btn btn-secondary" type="button" data-izimodal-close="">@lang("إلغاء")</button>
        </div>
      @endif
    @endif
  </div>
  @if($count)
    <div class="iziModal pr-1 pl-1" id="editModal" tab-index="-1" role="dialog" aria-labelledby="editModalTitle"
      aria-hidden="true">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalTitle">@lang("تعديل الطلب")</h5>
      </div>
      <div class="pl-1 pr-1">
        @if($reservation->longReservation)
          @component('components.edit-long-reservation', ["reservation" => $reservation,
                      'floors' => $floors, 'rooms' => $rooms])
          @endcomponent
          <div class="text-center">
            <button type="button" name="checkReservations" class="btn btn-info">
              @lang('تأكد من التوقيت')
            </button>
          </div>
          <div id="result">
          </div>
        @else
          @component('components.edit-temporary-reservation', ["reservation" => $reservation,
                      'floors' => $floors, 'rooms' => $rooms])
          @endcomponent
          <div class="text-center">
            <button type="button" name="checkReservations" class="btn btn-info mb-2" id="tempRes">
              @lang('تأكد')
            </button>
          </div>
          <div id="result"></div>
        @endif
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-izimodal-close="">@lang("إلغاء")</button>
        <button class="btn btn-success{{$reservation->longReservation ? '' : ' temp-accept'}}"
          type="button" id="acceptButtonModal" disabled>@lang("إرسال التعديل")</button>
      </div>
    </div>
  @endif
@endsection

@push("scripts")
  <script src="/js/iziModal.min.js" type="text/javascript"></script>
  <script src="/js/reservation.js?v=8"></script>
@endpush
