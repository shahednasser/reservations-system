@extends('master.authenticated', ["user" => $user])

@php
  $title = $type == "long" ? __("حجز مستمر") : __("حجز غير مستمر");
@endphp

@section("title", $title)

@section("stylesheets")
<link href="/css/print.min.css" rel="stylesheet" />
<link rel="stylesheet" href="/css/iziModal.min.css">
@endsection

@section("contents")
  @component('components.full-card')
    @slot("cardTitle")
      @component('components.buttonWithLoader',
                  ["classes" => "btn btn-outline-success ml-md-3 " . ($type == "long" ? 'long-res' : 'temp-res'),
                  "id" => 'printBtn', 'text' => __("طبع")])
      @endcomponent
      <div class="text-center">{{$title}}</div>
    @endslot
    @php
      $isAdmin = $user->isAdmin();
    @endphp
    @if($errors->count())
      <div class="alert alert-danger">
        @lang("الرجاء تعديل الأخطاء التالية والمحاولة مرة أخرى")
        <ul>
          @foreach($errors->getMessages() as $message)
            <li>{{$message[0]}}</li>
          @endforeach
        </ul>
      </div>
    @endif
    <div style="line-height: 2rem;">
      @if($type == "long")
        <div class="row">
          <div class="col-lg-6 col-md-6 col-12">
            <strong>@lang("إلى"): </strong>@lang("إدارة مركز ومسجد الحسن")
          </div>
          <div class="col-lg-6 col-md-6 col-12 text-left">
            <span class="pl-3"><strong>@lang("تاريخ تقديم الطلب"): </strong> {{date("Y-m-d")}}</span>
          </div>
        </div>
        <form action="/add-reservation/long" method="post">
          @csrf
          <div class="row">
            <div class="col-lg-4 col-md-4 col-12">
              <strong>@lang("من (مقدم الطلب)"): </strong>{{$user->name}}
            </div>
            <div class="col-lg-4 col-md-4 col-12">
              <strong>@lang("صفته"): </strong>{{$user->position}}
            </div>
            <div class="col-lg-4 col-md-4 col-12">
              <div class="form-group row">
                <label for="committee" class="col-sm-2 col-form-label"><strong>@lang("لجنة"): </strong></label>
                <div class="col-sm-10 pr-md-0">
                  <input type="text" name='committee' value="{{old('committee') ?: ''}}" class="form-control" required />
                </div>
              </div>
            </div>
          </div>
          <div class="mb-2">
            <strong>@lang("نرجو الموافقة على إقامة النشاط دوري مستمر ضمن حرم مركز ومسجد الحسن وفق المعلومات التالية"):</strong>
          </div>
          <div class="form-group">
            <label for="event_name"><strong>@lang("عنوان النّشاط"):</strong> </label>
            <input type="text" name="event_name" value="{{old('event_name') ?: ''}}" class="form-control" required />
          </div>
          @component('components.edit-long-reservation', ["reservation" => null, "floors" => $floors,
                      "rooms" => $rooms])
          @endcomponent
          <div class="form-group">
            <label for="notes"><strong>@lang("ملاحظات"):</strong> </label>
            <textarea class="form-control" name="notes">{{old('notes') ?: ''}}</textarea>
          </div>
          <div class="form-group">
            <label for="supervisors"><strong>@lang("الأساتذة المشرفون"): </strong></label>
            <input type="text" name="supervisors" value="{{old('supervisors') ?: ''}}" class="form-control" />
          </div>
          <div class="row">
            <div class="col-md-1 col-2 text-left">
              <input type="checkbox" name="pledge" class="form-control" required />
            </div>
            <div class="col-10">
              @component('components.pledge')
              @endcomponent
            </div>
          </div>
          <div class="text-center">
            @component('components.buttonWithLoader', ["classes" => 'btn btn-info',
                  'text' => __('تأكد من التوقيت'), "name" => "checkReservations",
                  "theme" => "dark"])
            @endcomponent
          </div>
          <div id="result" class="mt-2">
          </div>
          @if($isAdmin)
            <div class="text-left">
              <button class="btn btn-success" type="submit" name="submit" disabled>@lang("أضف الحجز")</button>
              <a href="{{url()->previous()}}" class="btn btn-secondary" role="button">
                @lang("إلغاء")
              </a>
            </div>
          @else
            <div class="text-left">
              <button class="btn btn-success" type="submit" disabled>@lang("أرسل الطلب")</button>
              <a href="{{url()->previous()}}" class="btn btn-secondary" role="button">
                @lang("إلغاء")
              </a>
            </div>
          @endif
        </form>
      @else
        <div class="row">
          <div class="col-lg-6 col-md-6 col-12">
            <small>*@lang("يرجى كتابة الوقت بصيغة 24/24 واستعمال الأرقام باللّغة الأجنبيّة")</small>
          </div>
          <div class="col-lg-6 col-md-6 col-12 text-left">
            <span class="pl-3"><strong>@lang("تاريخ تقديم الطلب"): </strong> {{date("Y-m-d")}}</span>
          </div>
        </div>
        <form action="/add-reservation/temporary" method="post">
          @csrf
          <div class="form-group">
            <label for="committee"><strong>@lang("اللجنة"): </strong></label>
            <input type="text" name='committee' value="{{old('committee') ?: ''}}" class="form-control" required />
          </div>
          <div>
            <strong>@lang("إلى"): </strong>@lang("إدارة مركز الحسن")
          </div>
          <div>
            @lang("نرجو الموافقة على إقامة النّشاط التّالي"):
          </div>
          <div class="form-group">
            <label for="event_name"><strong>@lang("عنوان النّشاط"):</strong> </label>
            <input type="text" name="event_name" value="{{old('event_name') ?: ''}}" class="form-control" required />
          </div>
          @component('components.edit-temporary-reservation', ["reservation" => null, "floors" => $floors,
                      "rooms" => $rooms])
          @endcomponent
          <div class="form-group">
            <label for="supervisors"><strong>@lang("المشرفون أثناء النّشاط"): </strong></label>
            <input type="text" name="supervisors" value="{{old('supervisors') ?: ''}}" class="form-control" />
          </div>
          <div class="form-group">
            <label for="equipment_needed"><strong>@lang("المستلزمات المطلوبة"): </strong></label>
            <input type="text" name="equipment_needed[]"
                   value="{{old('equipment_needed') && isset(old('equipment_needed')[0]) ? old('equipment_needed')[0] : ''}}"
                   class="form-control mb-3" />
            <input type="text" name="equipment_needed[]"
                   value="{{old('equipment_needed') && isset(old('equipment_needed')[1]) ? old('equipment_needed')[1] : ''}}"
                   class="form-control mb-3" />
            <input type="text" name="equipment_needed[]"
                   value="{{old('equipment_needed') && isset(old('equipment_needed')[2]) ? old('equipment_needed')[2] : ''}}"
                   class="form-control mb-3" />
          </div>
          <div class="form-group">
            <label for="notes"><strong>@lang('ملاحظات إضافية'):</strong> </label>
            <textarea class="form-control" name="notes">{{old('notes') ?: ''}}</textarea>
          </div>
          <div class="row">
            <div class="col-md-1 col-2 text-left">
              <input type="checkbox" name="pledge" class="form-control" required />
            </div>
            <div class="col-10">
              @component('components.pledge')
              @endcomponent
            </div>
          </div>
          <div class="text-center">
            @component('components.buttonWithLoader', ["classes" => 'btn btn-info mb-2', 'id' => 'tempRes',
                  'text' => __('تأكد'), "name" => "checkReservations",
                  "theme" => "dark"])
            @endcomponent
          </div>
          <div id="result" class="mt-2"></div>
          @if($isAdmin)
            <div class="text-left">
              <button class="btn btn-success" type="submit" name="submit" disabled>@lang("أضف الحجز")</button>
              <a href="{{url()->previous()}}" class="btn btn-secondary" role="button">
                @lang("إلغاء")
              </a>
            </div>
          @else
            <div class='text-left'>
              <button class="btn btn-success" type="submit" disabled>@lang("أرسل الطلب")</button>
              <a href="{{url()->previous()}}" class="btn btn-secondary" role="button">
                @lang("إلغاء")
              </a>
            </div>
          @endif
        </form>
      @endif
    </div>
  @endcomponent
@endsection

@push("scripts")
  <script src="/js/iziModal.min.js" type="text/javascript"></script>
  <script src="/js/print-reservation.js?v=8"></script>
  <script src="/js/reservation.js?v=8"></script>
@endpush
