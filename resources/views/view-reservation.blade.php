@extends('master.authenticated', ["user" => $user])
@php
  $title = "نشاط".($reservation->longReservation ? ' دوري مستمر' : ' غير مستمر');
@endphp
@section('title', $title)

@push("stylesheets")
  <link href="/css/reservation.css?v=8" rel="stylesheet" />
  <link rel="stylesheet" href="/css/iziModal.min.css">
@endpush

@section("contents")
  @component('components.full-card')
    @slot('cardTitle')
      @component('components.buttonWithLoader',
                  ["classes" => "btn btn-outline-success ml-md-3 " . ($reservation->longReservation ? 'long-res' : 'temp-res'),
                  "id" => 'printBtn', 'text' => __("طبع")])
      @endcomponent
      @php
        $stat = getFullStatus($reservation);
      @endphp
      <span class="status {{$stat[0]}} reservation-status">{{$stat[1]}}</span>
      <div class="text-center">{{$title}}</div>
      <span class="d-none" id="resId">{{$reservation->id}}</span>
    @endslot
    @if($user->isAdmin() || $user->id === $reservation->user->id)
        @if($reservation->is_approved == -1 && !$reservation->editedReservation)
          <div class='alert alert-danger'>
            تم رفض هذا الطلب.
            @if($reservation->reservationsRejection)
              الرسالة التالية مرفقة مع الرفض:<br>
              {{$reservation->reservationsRejection->message}}
            @endif
          </div>
        @endif
    @endif
    @if($reservation->message)
        <div class="alert alert-primary">
            <h5>@lang('شروط الحجز'):</h5>
            <p>
                {{$reservation->message}}
            </p>
        </div>
    @endif
    @if($reservation->pausedReservation)
      @component('components.paused-info', ["pausedReservation" => $reservation->pausedReservation])
      @endcomponent
    @endif
    @php
      $days = ["الإثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت", "الأحد"];
    @endphp

    <div class="reservation-details">
      <div id="toPrint">
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
                <th scope="col"></th>
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
        @else
          <div class="row">
            <div class="col-md-6 col-12"><strong>@lang("تاريخ تقديم الطلب"): </strong>{{format_date($reservation->date_created)}}</div>
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
          <ol>
            <li><strong>@lang("عنوان النشاط"):</strong> {{$reservation->event_name}}</li>
            <li>
              <ul style="list-style: none;">

                  @php
                    $dates = $reservation->temporaryReservation->temporaryReservationDates()->get()->toArray();
                    $i = 1;
                  @endphp
                  @foreach($dates as $date)
                    <li>
                      <strong style="{{$i == 1 ? '' : 'visibility: hidden;'}}">
                        @lang("تاريخ اقامته"):</strong>  - {{format_date($date['date'])}}
                      @lang("من الساعة"):
                        {{format_time_without_seconds($date['from_time'])}}
                      @lang("حتى الساعة"):
                        {{format_time_without_seconds($date['to_time'])}}
                    </li>
                    @php
                      if($i == 1){
                        $i++;
                      }
                    @endphp
                  @endforeach

              </ul>
            </li>
            <li>@lang("مكان النشاط"):
              @php
                $places = $reservation->temporaryReservation->temporaryReservationPlaces()->get();
              @endphp
              <ul style="list-style: none;">
                @foreach($places as $place)
                  <li>
                      -
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
            </li>
            <li>
              <strong>@lang("المشرفون أثناء النشاط"):</strong> {{$reservation->supervisors ? $reservation->supervisors : ''}}
            </li>
            <li>
              <strong>@lang("المستلزمات المطلوبة"): </strong>
              @if($reservation->temporaryReservation->equipment_needed_1)
                <div class="row">
                  <div class="col-12">
                    {{$reservation->temporaryReservation->equipment_needed_1}}
                  </div>
                </div>
              @endif
              @if($reservation->temporaryReservation->equipment_needed_2)
                <div class="row">
                  <div class="col-12">
                    {{$reservation->temporaryReservation->equipment_needed_2}}
                  </div>
                </div>
              @endif
              @if($reservation->temporaryReservation->equipment_needed_3)
                <div class="row">
                  <div class="col-12">
                    {{$reservation->temporaryReservation->equipment_needed_3}}
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
        @endif
      </div>
      <div class="text-left">
        @if($user->isAdmin())
          @if($reservation->is_approved != -1 && $reservation->is_approved != -2)
              @if(!$reservation->pausedReservation)
                  <a href="#" data-izimodal-open="#editModal" data-izimodal-transitionin="fadeInDown"
                     class="btn btn-success">@lang("تعديل")</a>
              @endif
              <button class="btn btn-danger" id="deleteButton" data-izimodal-open="#deleteModal"
                data-izimodal-transitionin="fadeInDown">
                  @lang("إلغاء الطلب")
              </button>
          @endif
        @else
          @if($reservation->is_approved != -2 && $reservation->user()->withTrashed()->first()->id == $user->id)
            @if($reservation->is_approved != 1)
              <a class="btn btn-success" href="/edit-reservation/{{$reservation->id}}">@lang("تعديل")</a>
              @if($reservation->editedReservation)
                <button class="btn btn-success" data-izimodal-open="#approveModal"
                data-izimodal-transitionin="fadeInDown">
                  @lang("الموافقة على التعديل")
                </button>
              @endif
              <button class="btn btn-danger" id="deleteButton" data-izimodal-open="#deleteModal"
              data-izimodal-transitionin="fadeInDown" type="button">
                @lang("إلغاء الطلب")
              </button>
            @else
              @if(!$reservation->pausedReservation &&
              (!$reservation->hasEditRequest || $reservation->hasEditRequest->newReservation->is_approved == -1))
                <a class="btn btn-success" href="/edit-reservation/{{$reservation->id}}">@lang("طلب تعديل")</a>
              @endif
              <button class="btn btn-danger" id="deleteButton" data-izimodal-open="#deleteModal"
              data-izimodal-transitionin="fadeInDown">
                @lang("طلب إلغاء")
              </button>
            @endif
          @endif
        @endif
        @if($user->isAdmin() || $reservation->user->id === $user->id)
          @if(!$reservation->pausedReservation)
            <button class="btn btn-secondary" id="pauseButton" data-izimodal-open="#pauseModal"
            data-izimodal-transitionin="fadeInDown">
              @lang("توقيف الحجز لوقت مؤقت")
            </button>
          @else
            <button class="btn btn-secondary" id="pauseButton" data-izimodal-open="#pauseModal"
                    data-izimodal-transitionin="fadeInDown">
                @lang("تعديل فترة توقيف الحجز")
            </button>
            <button class="btn btn-danger" id="deletePauseButton" data-izimodal-open="#deletePauseModal"
                    data-izimodal-transitionin="fadeInDown">
                @lang("إلغاء إيقاف الحجز")
            </button>
          @endif
        @endif
      </div>
    </div>
  @endcomponent
@endsection

@section("modals")
  @if($reservation->is_approved != -2)
    @if($user->isAdmin() && !$reservation->pausedReservation)
        <div class="iziModal pr-1 pl-1" id="editModal" tab-index="-1" role="dialog" aria-labelledby="editModalTitle"
             aria-hidden="true">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalTitle">@lang("تعديل الطلب")</h5>
            </div>
            @if($reservation->longReservation)
                @component('components.edit-long-reservation', ["reservation" => $reservation,
                            'floors' => $floors, 'rooms' => $rooms])
                @endcomponent
                <div class="text-center mt-2">
                    @component('components.buttonWithLoader',
                                ["classes" => 'btn btn-info',
                                'text' => __('تأكد من التوقيت'),
                                'name' => 'checkReservations',
                                'theme' => 'dark'])
                    @endcomponent
                </div>
                <div id="result">
                </div>
            @else
                @component('components.edit-temporary-reservation', ["reservation" => $reservation,
                            'floors' => $floors, 'rooms' => $rooms])
                @endcomponent
                <div class="text-center mt-2">
                    @component('components.buttonWithLoader',
                                ["classes" => 'btn btn-info mb-2',
                                'text' => __('تأكد من التوقيت'),
                                'name' => 'checkReservations',
                                'id' => 'tempRes',
                                'theme' => 'dark'])
                    @endcomponent
                </div>
                <div id="result"></div>
            @endif
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-izimodal-close="">@lang("إلغاء")</button>
                <button class="btn btn-success{{$reservation->longReservation ? '' : ' temp-accept'}}"
                        type="button" id="acceptButtonModal" disabled>@lang("تعديل")</button>
            </div>
        </div>
    @endif
    <div class="iziModal pr-1 pl-1" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle"
         aria-hidden="true">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalTitle">@lang("تأكيد الرفض")</h5>
      </div>
      <div class="pt-3 pb-3">
        @if($reservation->is_approved === 1 && !$user->isAdmin())
          @lang("هل أنت متأكد من أرسال طلب إلغاء للنشاط؟")
        @else
          @lang("هل أنت متأكد من إلغاء الطلب؟")
        @endif
      </div>
      <div class="modal-footer">
        <a class="btn btn-danger" role="button" href="/delete-reservation/{{$reservation->id}}">@lang("نعم")</a>
        <button class="btn btn-secondary" type="button" data-izimodal-close="">@lang("كلا")</button>
      </div>
    </div>
    @if($user->isAdmin() || $reservation->user->id === $user->id)
      @if($reservation->pausedReservation)
          @component('components.pause-reservation-modal', ["status" => "edit",
              "from_date" => $reservation->pausedReservation->from_date,
              "to_date" => $reservation->pausedReservation->to_date, "id" => $reservation->id,
              "places" => getPlaces($reservation),
              "selectedPlaces" => $reservation->pausedReservation->pausedReservationPlaces()->get(),
              "class" => get_class($reservation)])
          @endcomponent
          <div class="iziModal pr-1 pl-1" id="deletePauseModal" tabindex="-1" role="dialog" aria-labelledby="deletePauseModalTitle"
               aria-hidden="true">
              <div class="modal-header">
                  <h5 class="modal-title" id="deletePauseModalTitle">@lang("تأكيد الرفض")</h5>
              </div>
              <div class="pt-3 pb-3">
                  @lang("هل أنت متأكد من إلغاء إيقاف الحجز؟")
              </div>
              <div class="modal-footer">
                  <button class="btn btn-danger delete-pause-reservation" type="button" id="deletePauseReservation_{{$reservation->id}}">@lang("نعم")</button>
                  <button class="btn btn-secondary" type="button" data-izimodal-close="">@lang("كلا")</button>
              </div>
          </div>
      @else
        @component('components.pause-reservation-modal', ["status" => "add", "id" => $reservation->id,
                    "places" => getPlaces($reservation), "class" => get_class($reservation)])
        @endcomponent 
      @endif
    @endif
  @endif
@endsection

@push("scripts")
  <script src="/js/iziModal.min.js" type="text/javascript"></script>
  <script src="/js/reservation.js?v=8"></script>
  <script src="/js/print-reservation.js?v=8"></script>
  <script src="/js/mass-editing.js?v=8"></script>
@endpush
