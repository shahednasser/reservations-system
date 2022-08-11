@extends('master.authenticated', ["user" => $user])
@php
  $title = __('حجز قاعة')
@endphp

@section('title', $title)

@push("stylesheets")
  <link href="/css/admin-add-reservation.css?v=8" rel="stylesheet" />
  <link rel="stylesheet" href="/css/iziModal.min.css">
@endpush

@section('contents')
  @component('components.full-card')
    @slot('cardTitle')
      @component('components.buttonWithLoader', ["classes" => 'btn btn-outline-success ml-3 manual-res',
                  "id" => 'printBtn', 'text' => __("طبع")])
      @endcomponent
      @php
        $stat = getFullStatus($reservation);
      @endphp
      <span class="status {{$stat[0]}} reservation-status">{{$stat[1]}}</span>
      <div class="text-center">{{$title}}</div>
      <span class="d-none" id="resId">{{$reservation->id}}</span>
    @endslot

    @if($reservation->pausedReservation)
      @component('components.paused-info', ["pausedReservation" => $reservation->pausedReservation])
      @endcomponent
    @endif
    <div class="row mb-4">
      <div class="col-sm-8 col-12">
        <strong>@lang('الإسم الثلاثي لطالب الحجز'):</strong> {{$reservation->full_name}}
      </div>
      <div class="col-sm-4 col-12">
        <strong>@lang('جمعية'):</strong> {{$reservation->organization ? $reservation->organization : ''}}
      </div>
    </div>
    <div class="row mb-4">
      <div class="col-sm-8 col-12">
        <strong>@lang('خليوي'):</strong> {{$reservation->mobile_phone ? $reservation->mobile_phone : ''}}
      </div>
      <div class='col-sm-4 col-12'>
        <strong>@lang('أرضي'):</strong> {{$reservation->home_phone ? $reservation->home_phone : ''}}
      </div>
    </div>
    <div class="row mb-4">
      <div class="col-md-4 col-sm-6 col-12">
        <strong>@lang('العنوان'):</strong> {{$reservation->event_name}}
      </div>
      <div class="col-md-4 col-sm-6 col-12">
        <strong>@lang('نوع المناسبة'):</strong> {{$reservation->event_type}}
      </div>
      <div class="col-md-4 col-sm-6 col-12">
        <strong>@lang('تاريخ تقديم الطلب')</strong> {{format_date($reservation->date_created)}}
      </div>
    </div>
    <table class="table responsive-table table-bordered timing-table">
      <thead class="thead-dark">
        <tr>
          <th class="w-15">
            @lang("التوقيت")
          </th>
        </tr>
      </thead>
      <tbody>
        @php
          $nb = 0;
        @endphp
        @foreach($reservation->manualReservationsDates()->get() as $date)
          @php
            $nb++;
          @endphp
          <tr>
            <td>
              <strong>@lang("التاريخ")</strong>: {{format_date($date->date)}}
            </td>
            <td>
              <strong>@lang("من الساعة")</strong>: {{format_time_without_seconds($date->from_time)}}
            </td>
            <td>
              <strong>@lang("من الساعة")</strong>: {{format_time_without_seconds($date->to_time)}}
            </td>
            <td>
              <strong>@lang("رجال")</strong>: {{$date->for_men ? '✔' : ''}}
            </td>
            <td>
              <strong>@lang("نساء")</strong>: {{$date->for_women ? '✔' : ''}}
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="requirments-container">
      <div class="accordion" id="requirmentsAccordion">
        <div class="card">
          <div class="card-header bg-success" id="placeRequirmentsHeader">
            <div class="row align-items-baseline">
              <div class="col-6">
                <h5 class="mb-0">
                  <button class="btn btn-link text-white" type="button"
                    data-toggle="collapse" data-target="#placeRequirments" aria-expanded="true" aria-controls="placeRequirments">
                    @lang("مستلزمات القاعة")
                  </button>
                </h5>
              </div>
              <div class="col-6 text-left text-white">
                <small><strong>@lang("المجموع"):
                  <span class="grand-req-total">0</span>$
                </strong></small>
              </div>
            </div>
          </div>
          <div class="collapse" id="placeRequirments" aria-labelledby="placeRequirmentsHeader" data-parent="#requirmentsAccordion">
            <div class="card-body">
              @foreach($placeRequirments as $placeRequirment)
                @php
                  $mpr = $reservation->manualPlaceRequirments()->where('place_requirment_id', $placeRequirment->id)->first();
                @endphp
                <div class="list-group">
                  <a class='list-group-item list-group-item-action flex-column align-items-start'>
                    <div class="row">
                      <div class='col-6'>
                        <h4>{{$placeRequirment->name}}</h4>
                      </div>
                      <div class="col-6 text-left">
                        <small><strong>@lang("المجموع"):
                          <span id="requirmentTotalPrice_{{$placeRequirment->id}}">0</span>$
                        </strong></small>
                      </div>
                    </div>
                    <div class="mt-3">
                      <span>@lang("السعر"): </span>
                      <strong id="requirmentSinglePrice_{{$placeRequirment->id}}">{{$placeRequirment->price}}</strong>$
                    </div>
                    <div class="row mt-3">
                      <div class="col-md-3 col-12">
                        <div class="form-group">
                          <strong>@lang("عدد الأيام")</strong>:
                          <span id="place_requirment_{{$placeRequirment->id}}" class="req-num">{{$mpr ? $mpr->nb_days : 0}}</span>
                        </div>
                      </div>
                      <div class="col-md-9 col-12">
                        <div class="form-group">
                          <strong>@lang("التاريخ")</strong>:
                          @if($mpr)
                            @foreach($mpr->manualPlaceRequirmentsDates()->get() as $mprd)
                              <div>{{format_date($mprd->manualReservationsDate->date)}}</div>
                            @endforeach
                          @endif
                        </div>
                      </div>
                    </div>
                  </a>
                </div>
              @endforeach
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-header bg-success" id="hospitalityRequirmentsHeader">
            <div class="row align-items-baseline">
              <div class="col-6">
                <h5 class="mb-0">
                  <button class="btn btn-link text-white" type="button"
                    data-toggle="collapse" data-target="#hospitalityRequirments" aria-expanded="true"
                     aria-controls="hospitalityRequirments">
                    @lang("مستلزمات الضيافة")
                  </button>
                </h5>
              </div>
              <div class="col-6 text-left text-white">
                <small><strong>@lang("المجموع"):
                  <span class="grand-req-total">0</span>$
                </strong></small>
              </div>
            </div>
          </div>
          <div class="collapse" id="hospitalityRequirments" aria-labelledby="hospitalityRequirmentsHeader" data-parent="#requirmentsAccordion">
            <div class="card-body">
              @foreach($hospitalityRequirments as $hospitalityRequirment)
                @php
                  $mhr = $reservation->manualHospitalityRequirments()->get()
                                    ->where('hospitality_requirment_id', $hospitalityRequirment->id)->first();
                @endphp
                <div class="list-group">
                  <a class='list-group-item list-group-item-action flex-column align-items-start'>
                    <div class="row">
                      <div class='col-6'>
                        <h4>{{$hospitalityRequirment->name}}</h4>
                        @if($mhr && $mhr->additional_name)
                          <h6>{{$mhr->additional_name}}</h6>
                        @endif
                      </div>
                      <div class="col-6 text-left">
                        <small><strong>@lang("المجموع"):
                          <span id="requirmentTotalPrice_{{$hospitalityRequirment->id}}">0</span>$
                        </strong></small>
                      </div>
                    </div>
                    <div class="mt-3">
                      <span>@lang("السعر الفردي"): </span>
                      <strong id="requirmentSinglePrice_{{$hospitalityRequirment->id}}">{{$hospitalityRequirment->price ?
                        $hospitalityRequirment->price : ($mhr ? $mhr->additional_price : 0)}}</strong>$
                    </div>
                    <div class="form-group mt-3">
                      <strong>@lang("عدد الأيام")</strong>
                      <span id="hospitality_requirment_nb_{{$hospitalityRequirment->id}}"
                        class="req-num"  />{{$mhr ? $mhr->nb_days : 0 }}</span>
                    </div>
                  </a>
                </div>
              @endforeach
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-header bg-success" id="religiousRequirmentsHeader">
            <div class="row align-items-baseline">
              <div class="col-6">
                <h5 class="mb-0">
                  <button class="btn btn-link text-white" type="button"
                    data-toggle="collapse" data-target="#religiousRequirments" aria-expanded="true" aria-controls="religiousRequirments">
                    @lang("المستلزمات الدينية")
                  </button>
                </h5>
              </div>
              <div class="col-6 text-left text-white">
                <small><strong>@lang("المجموع"):
                  <span class="grand-req-total">0</span>$
                </strong></small>
              </div>
            </div>
          </div>
          <div class="collapse" id="religiousRequirments" aria-labelledby="religiousRequirmentsHeader" data-parent="#requirmentsAccordion">
            <div class="card-body">
              @foreach($religiousRequirments as $religiousRequirment)
                @php
                  $mrr = $reservation->manualReligiousRequirments()->get()
                          ->where('religious_requirment_id', $religiousRequirment->id)->first();
                @endphp
                <div class="list-group">
                  <a class='list-group-item list-group-item-action flex-column align-items-start'>
                    <div class="row">
                      <div class='col-6'>
                        <h4>{{$religiousRequirment->name}}</h4>
                      </div>
                      <div class="col-6 text-left">
                        <small><strong>@lang("المجموع"):
                          <span id="requirmentTotalPrice_{{$religiousRequirment->id}}">0</span>$
                        </strong></small>
                      </div>
                    </div>
                    <div class="mt-3">
                      <span>@lang("السعر الفردي"): </span>
                      <strong>
                        <span id="requirmentSinglePrice_{{$religiousRequirment->id}}">{{$religiousRequirment->price}}</span>$
                      </strong>
                    </div>
                    <div class="form-group mt-3">
                      <strong>@lang("عدد الأيام")</strong>
                      <span id="religious_requirment_nb_{{$religiousRequirment->id}}" class="req-num">
                        {{ $mrr ? $mrr->nb_days : 0 }}
                      </span>
                    </div>
                  </a>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="w-100">
      <div class="total-container text-left p-3 bg-success border-bottom">
        <span class="text-white">@lang("خصم"):
          <span id='discount'>{{$reservation->discount}}</span>$
        </span>
      </div>
    </div>
    <div class="w-100">
      <div class="total-container text-left p-3 bg-success mb-5">
        <span class="text-white">@lang("المجموع العام"):
          <span id='grandTotal'>0</span>$
        </span>
      </div>
    </div>
    <table class="table responsive-table bordered-table mt-5 mb-5">
      <thead>
        <tr class="bg-dark text-white">
          <th colspan="4" class="text-center">
            @lang("عدد المستلزمات")
          </th>
        </tr>
        <tr>
          <th>@lang("المستلزمات")</th>
          @php
            $days = ["اليوم الأول", "اليوم الثاني", "اليوم الثالث"];
          @endphp
          @for($i = 0; $i < $nb; $i++)
            <th>@lang($days[$i])</th>
          @endfor
        </tr>
      </thead>
      <tbody>
        @foreach($equipments as $equipment)
          @php
            $mre = [];
          @endphp
          <tr>
            <th>{{$equipment->name}}</th>
            @for($i = 1; $i <= $nb; $i++)
              @php
                $mre[$i] = $reservation->manualReservationEquipments()->where('equipment_id', $equipment->id)
                          ->where('day_nb', $i)->first();
              @endphp
                <td>{{$mre[$i] ? $mre[$i]->number : 0}}</td>
            @endfor
          </tr>
        @endforeach
      </tbody>
    </table>
  @endcomponent
  @if($user->isAdmin() && $reservation->is_approved === 1)
    <div class="text-center mb-5">
      @if(!$reservation->pausedReservation)
        <a href="/edit-admin-reservation/{{$reservation->id}}" class="btn btn-success" role="button">
          @lang("تعديل")
        </a>
      @endif
      <button data-izimodal-open="#deleteModal" data-izimodal-transitionin="fadeInDown"
      class="btn btn-danger" role="button">
        @lang("حذف")
      </button>
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
    </div>
  @endif
@endsection

@section("modals")
  @if($user->isAdmin())
    <div class="iziModal pr-1 pl-1" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle"
      aria-hidden="true">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalTitle">@lang("تأكيد الحذف")</h5>
      </div>
      <div class="pt-3 pb-3">
        @lang("هل أنت متأكد من حذف الحجز")
      </div>
      <div class="modal-footer">
        <a class="btn btn-danger" role="button" href="/delete-admin-reservation/{{$reservation->id}}">@lang("نعم")</a>
        <button class="btn btn-secondary" type="button" data-izimodal-close="">@lang("كلا")</button>
      </div>
    </div>
  @endif
  @if($user->isAdmin())
    @if($reservation->pausedReservation)
      @component('components.pause-reservation-modal', ["status" => "edit", "from_date" =>
                  $reservation->pausedReservation->from_date, "to_date" => $reservation->pausedReservation->to_date,
                  "id" => $reservation->id, "class" => get_class($reservation)])
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
          <button class="btn btn-danger delete-pause-reservation" type="button"
                  id="deletePauseReservation_{{$reservation->id}}">@lang("نعم")</button>
          <button class="btn btn-secondary" type="button" data-izimodal-close="">@lang("كلا")</button>
        </div>
      </div>
    @else
      @component('components.pause-reservation-modal', ["status" => "add", "id" => $reservation->id,
        "class" => get_class($reservation)])
      @endcomponent
    @endif
  @endif
@endsection

@push("scripts")
  <script src="/js/iziModal.min.js" type="text/javascript"></script>
  <script src='/js/view-admin-reservation.js?v=8'></script>
  <script src="/js/print-reservation.js?v=8"></script>
  <script src="/js/mass-editing.js?v=8"></script>
@endpush
