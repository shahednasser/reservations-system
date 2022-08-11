@extends('master.authenticated', ["user" => $user])

@php
  $title = __("حجز قاعة مركز الحسن");
@endphp

@push("stylesheets")
  <link href="/css/admin-add-reservation.css?v=8" rel="stylesheet" />
  <link rel="stylesheet" href="/css/iziModal.min.css">
@endpush

@section("title", $title)

@section("contents")
  @component('components.full-card')
    @slot("cardTitle")
      @component('components.buttonWithLoader',
                  ["classes" => "btn btn-outline-success ml-lg-3 manual-res",
                  "id" => 'printBtn', 'text' => __("طبع")])
      @endcomponent
      <div class="text-center">{{$title}}</div>
    @endslot
    @if(count($errors))
      <div class="alert alert-danger">
        @lang('الرجاء تعديل المشاكل التالية وإعادة المحاولة'):
        <ul>
          @foreach($errors->getMessages() as $errorArr)
            @foreach($errorArr as $error)
              <li>{{$error}}</li>
            @endforeach
          @endforeach
        </ul>
      </div>
    @endif
    <form action="{{isset($reservation) && $reservation ? '/edit-admin-reservation/'.$reservation->id : '/admin-add-reservation'}}" method="post">
      @csrf
      <div class="form-group">
        <label for="name">@lang("الإسم الثلاثي لطالب الحجز")</label>
        @php
          $name = isset($reservation) ? $reservation->full_name : old('name');
        @endphp
        <input name="name" type='text' class="form-control" required
          value="{{$name ? $name : ''}}" />
      </div>
      <div class="row">
        <div class="col-md-4 col-12">
          <div class="form-group">
            <label for="organization">@lang('جمعية')</label>
            <input name="organization" type="text" class="form-control"
              value="{{isset($reservation) ? $reservation->organization : (old('organization') ? old('organization') : '')}}" />
          </div>
        </div>
        <div class="col-md-4 col-12">
          <div class="form-group">
            <label for="mobilePhone">@lang("خليوي")</label>
            <input type="tel" minLength="8" class="form-control" name="mobilePhone"
              value="{{isset($reservation) ? $reservation->mobile_phone : (old('mobilePhone') ? old('mobilePhone') : '')}}" />
          </div>
        </div>
        <div class="col-md-4 col-12">
          <div class="form-group">
            <label for="homePhone">@lang("أرضي")</label>
            <input type="tel" minLength="8" class="form-control" name="homePhone"
               value="{{isset($reservation) ? $reservation->home_phone : (old('homePhone') ? old('homePhone') : '')}}" />
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-4 col-12">
          <div class="form-group">
            <label for="eventName">@lang("العنوان")</label>
            <input type="text" class="form-control" name="eventName"
             value="{{isset($reservation) ? $reservation->event_name : (old('eventName') ? old('eventName') : '')}}" />
          </div>
        </div>
        <div class="col-md-4 col-12">
          <div class="form-group">
            <label for="eventType">@lang("نوع المناسبة")</label>
            <input type="text" class="form-control" name="eventType"
              value="{{isset($reservation) ? $reservation->event_type : (old('eventType') ? old('eventType') : '')}}"
              required />
          </div>
        </div>
        <div class="col-md-4 col-12">
          <div class="form-group">
            <label for="date_created">@lang("تاريخ تقديم الطلب")</label>
              @component('components.date-input', ["value" => isset($reservation) ? $reservation->date_created : (old('date_created') ?: date('Y-m-d')),
              "name" => "date_created", "isDisabled" => false, "isRequired" => true])
              @endcomponent
          </div>
        </div>
      </div>
      <table class="table reservations-table table-bordered timing-table">
        <thead class="thead-dark">
          <tr>
            <th class="w-15">
              @lang("التوقيت")
            </th>
          </tr>
        </thead>
        <tbody>
          @php
            $mrds = [];
            $nb_days = 0;
            if(isset($reservation)){
              $mrds = $reservation->manualReservationsDates()->get();
              $mrdsArr = $mrds->toArray();
            }
          @endphp
          @for($i = 0; $i < 3; $i++)
          @php
            $mrd = null;
            if(count($mrds) > $i){
              $mrd = $mrdsArr[$i];
              $nb_days++;
            }
          @endphp
            <tr>
              <td class="text-center align-middle">
                <input type="checkbox" class="form-control" name="dates_{{$i}}"
                  {{$mrd ? 'checked' : (old('dates_'.$i) ? 'checked' : '')}} />
              </td>
              <td>
                <label for="date_{{$i}}">@lang("التاريخ")</label>
                @component('components.date-input', ["value" => ($mrd ? $mrd['date'] : (old('date_'.$i) ? old('date_'.$i) : '')), "name" => "date_$i",
                  "isDisabled" => !old('dates_'.$i) && !$mrd, "isRequired" => false])
                @endcomponent
              </td>
              <td>
                <label for="from_time_{{$i}}">@lang("من الساعة")</label>
                @component('components.time-input', ['name' => 'from_time_'.$i,
                            'value' =>  $mrd ? format_time_without_seconds($mrd['from_time']) :
                              (old('from_time_'.$i) ? old('from_time_'.$i) : null),
                            'isDisabled' => !$mrd && !old('dates_'.$i) ? true : false])
                @endcomponent
              </td>
              <td>
                <label for="to_time_{{$i}}">@lang("من الساعة")</label>
                @component('components.time-input', ['name' => 'to_time_'.$i,
                            'value' =>  $mrd ? format_time_without_seconds($mrd['to_time']) :
                              (old('to_time_'.$i) ? old('to_time_'.$i) : null),
                            'isDisabled' => !$mrd && !old('dates_'.$i) ? true : false])
                @endcomponent
              </td>
              <td>
                <label for="men_{{$i}}">@lang("رجال")</label>
                <input type="checkbox" name="men_{{$i}}" class="form-control" {{!$mrd && !old('dates_'.$i) ? 'disabled' : ''}}
                  {{$mrd ? ($mrd["for_men"] ? 'checked' : '') : (old('men_'.$i) ? 'checked' : '')}} />
              </td>
              <td>
                <label for="women_{{$i}}">@lang("نساء")</label>
                <input type="checkbox" name="women_{{$i}}" class="form-control" {{!$mrd && !old('dates_'.$i) ? 'disabled' : ''}}
                 {{$mrd ? ($mrd["for_women"] ? 'checked' : '') : (old('women_'.$i) ? 'checked' : '')}} />
              </td>
            </tr>
          @endfor
        </tbody>
      </table>
      <div class="text-center mb-3">
        @component('components.buttonWithLoader',
                    ["classes" => "btn btn-info",
                    "id" => 'checkReservations', 'text' => __("تأكد من التوقيت"),
                    'theme' => 'dark'])
        @endcomponent
      </div>
      <div id="result"></div>
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
                @php
                  $mprs = null;
                  if(isset($reservation)){
                    $mprs = $reservation->manualPlaceRequirments()->get();
                  }
                @endphp
                @foreach($placeRequirments as $placeRequirment)
                  @php
                    $mpr = null;
                    if($mprs){
                      $mpr = $mprs->where("place_requirment_id", $placeRequirment->id)->first();
                      $mprds = [];
                      if($mpr){
                        $mprds = $mpr->manualPlaceRequirmentsDates()->get();
                      }
                    }
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
                            <label for="place_requirment_{{$placeRequirment->id}}">@lang("عدد الأيام")</label>
                            <input type="number" name="place_requirment_{{$placeRequirment->id}}" class="form-control req-num"
                              min='0' max="3" value="{{$mpr ? $mpr->nb_days : 0}}" />
                          </div>
                        </div>
                        <div class="col-md-9 col-12">
                          <div class="form-group">
                            <label for="place_requirment_{{$placeRequirment->id}}">@lang("التاريخ")</label>
                            <select name="place_requirment_dates_{{$placeRequirment->id}}[]" multiple class="form-control">
                              @if(isset($reservation))
                                @foreach($mrdsArr as $key => $mrdArr)
                                  @php
                                    $mrd = $mrds->where("id", $mrdArr['id'])->first();
                                    $manualPlaceRequirmentDates = $mrd->manualPlaceRequirmentsDates()->get();
                                    $intersect = $manualPlaceRequirmentDates->intersect($mprds);

                                  @endphp
                                  <option value="{{$key}}" {{$intersect->count() ? 'selected' : ''}}>{{format_date($mrd->date)}}</option>
                                @endforeach
                              @endif
                            </select>
                          </div>
                        </div>
                      </div>
                    </a>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
          @php
            $mhrs = null;
            if(isset($reservation)){
              $mhrs = $reservation->manualHospitalityRequirments()->get();
            }
          @endphp
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
                    $mhr = null;
                    if($mhrs){
                      $mhr = $mhrs->where("hospitality_requirment_id", $hospitalityRequirment->id)->first();
                    }
                  @endphp
                  <div class="list-group">
                    <a class='list-group-item list-group-item-action flex-column align-items-start'>
                      <div class="row">
                        <div class='col-6'>
                          <h4>{{$hospitalityRequirment->name}}</h4>
                        </div>
                        <div class="col-6 text-left">
                          <small><strong>@lang("المجموع"):
                            <span id="requirmentTotalPrice_{{$hospitalityRequirment->id}}">0</span>$
                          </strong></small>
                        </div>
                      </div>
                      @if($hospitalityRequirment->price)
                        <div class="mt-3">
                          <span>@lang("السعر الفردي"): </span>
                          <strong id="requirmentSinglePrice_{{$hospitalityRequirment->id}}">{{$hospitalityRequirment->price}}</strong>$
                        </div>
                        <div class="form-group mt-3">
                          <label for="hospitality_requirment_nb_{{$hospitalityRequirment->id}}">@lang("عدد الأيام")</label>
                          <input type="number" name="hospitality_requirment_nb_{{$hospitalityRequirment->id}}"
                            class="form-control req-num" min="0" max="3"
                            value="{{$mhr ? $mhr->nb_days : 0}}" />
                        </div>
                      @else
                        <div class="mt-3">
                          <input type="text" name="hospitality_requirment_additional_name_{{$hospitalityRequirment->id}}"
                            class="form-control"
                            value="{{$mhr ? $mhr->additional_name : (old('hospitality_requirment_additional_name_'.$hospitalityRequirment->id) ?
                            old('hospitality_requirment_additional_name_'.$hospitalityRequirment->id) : '')}}" />
                        </div>
                        <div class="mt-3">
                          <span>@lang("السعر الفردي"): </span>
                          <input type="number" name="requirmentSinglePrice_{{$hospitalityRequirment->id}}"
                            class="form-control" id="requirmentSinglePrice_{{$hospitalityRequirment->id}}"
                            value="{{$mhr ? $mhr->additional_price : 0}}" />
                        </div>
                        <div class="form-group mt-3">
                          <label for="hospitality_requirment_nb_{{$hospitalityRequirment->id}}">@lang("عدد الأيام")</label>
                          <input type="number" name="hospitality_requirment_nb_{{$hospitalityRequirment->id}}"
                            class="form-control req-num" min="0" max="3"
                            value="{{$mhr ? $mhr->nb_days : 0}}" />
                        </div>
                      @endif
                    </a>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
          @php
            $mrrs = null;
            if(isset($reservation)){
              $mrrs = $reservation->manualReligiousRequirments()->get();
            }
          @endphp
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
                    $mrr = null;
                    if($mrrs){
                      $mrr = $mrrs->where("religious_requirment_id", $religiousRequirment->id)->first();
                    }
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
                        <label for="religious_requirment_nb_{{$religiousRequirment->id}}">@lang("عدد الأيام")</label>
                        <input type="number" name="religious_requirment_nb_{{$religiousRequirment->id}}" class="form-control req-num"
                              min="0" max="3"
                              value="{{$mrr ? $mrr->nb_days : 0}}" />
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
          <input type="number" name="discount" class="form-control" placeholder="{{__('خصم')}}"
            value="{{isset($reservation) ? $reservation->discount : (old('reservation') ? old('reservation') : '')}}" />
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
            <th>@lang("اليوم الأول")</th>
            <th>@lang("اليوم الثاني")</th>
            <th>@lang("اليوم الثالث")</th>
          </tr>
        </thead>
        <tbody>
          @php
            $mres = null;
            if(isset($reservation)){
              $mres = $reservation->manualReservationEquipments()->get();
            }
          @endphp
          @foreach($equipments as $equipment)
            @php
              $mre1 = null;
              $mre2 = null;
              $mre3 = null;
              if($mres){
                $mre1 = $mres->where("equipment_id", $equipment->id)->where("day_nb", 1)->first();
                $mre2 = $mres->where("equipment_id", $equipment->id)->where("day_nb", 2)->first();
                $mre3 = $mres->where("equipment_id", $equipment->id)->where("day_nb", 3)->first();
              }
            @endphp
            <tr>
              <th>{{$equipment->name}}</th>
              <td><input name="equipment_nb_1_{{$equipment->id}}" class="form-control" type="number"
                    min="0" {{!$mre1 && $nb_days < 1 ? 'disabled' : ''}} value="{{$mre1 ? $mre1->number :
                      (old('equipment_nb_1_'.$equipment->id) ? old('equipment_nb_1_'.$equipment->id) : '0')}}" /></td>
              <td><input name="equipment_nb_2_{{$equipment->id}}" class="form-control" type="number"
                    min="0" {{!$mre2 && $nb_days < 2 ? 'disabled' : ''}} value="{{$mre2 ? $mre2->number :
                    (old('equipment_nb_2_'.$equipment->id) ? old('equipment_nb_2_'.$equipment->id) : '0')}}" /></td>
              <td><input name="equipment_nb_3_{{$equipment->id}}" class="form-control" type="number"
                    min="0" {{!$mre3 && $nb_days < 3 ? 'disabled' : ''}} value="{{$mre3 ? $mre3->number :
                      (old('equipment_nb_3_'.$equipment->id) ? old('equipment_nb_3_'.$equipment->id) : '0')}}" /></td>
            </tr>
          @endforeach
        </tbody>
      </table>
      <div class="text-center">
        @if(isset($reservation))
          <button class="btn btn-success" type="submit" disabled>@lang("عدل الحجز")</button>
          <button class="btn btn-secondary" type="button" data-izimodal-open="#deleteModal"
          data-izimodal-transitionin="fadeInDown">
            @lang("إلغاء التعديل")
          </button>
        @else
          <button class="btn btn-success" type="submit" disabled>@lang("أضف الحجز")</button>
        @endif
      </div>
    </form>


  @endcomponent
@endsection

@section("modals")
@if(isset($reservation))
<div class="iziModal pr-1 pl-1" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle"
  aria-hidden="true">
  <div class="modal-header">
    <h5 class="modal-title" id="deleteModalTitle">@lang("إلغاء التعديل")</h5>
  </div>
  <div class="pt-3 pb-3">
    @lang("هل أنت متأكد من إلغاء التعديل؟")
  </div>
  <div class="modal-footer">
    <a class="btn btn-danger" role="button" href="/view-admin-reservation/{{$reservation->id}}">@lang("نعم")</a>
    <button class="btn btn-secondary" type="button" data-izimodal-close="">@lang("كلا")</button>
  </div>
</div>
@endif
@endsection

@push("scripts")
  <script src="/js/iziModal.min.js" type="text/javascript"></script>
  <script src="/js/admin-add-reservation.js?v=8"></script>
  <script src="/js/print-reservation.js?v=8"></script>
@endpush
