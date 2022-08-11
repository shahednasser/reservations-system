<div>
  @if(isset($date))
    <div class="d-flex justify-content-center mb-sm-0 mb-2 mr-sm-2 change-date">
      @component("components.date-input", ["name" => "calendarDate", "id" => "calendarDate",
        "classes" => "align-self-center ml-2", "isDisabled" => false, "isRequired" => false,
        "value" => date_format($date, "Y-m-d")])
      @endcomponent
      <button type="button" class="btn btn-outline-success" id="calendarDateSubmit">
        @lang("غير التاريخ")
      </button>
    </div>
  @endif
  <div class="legends rounded border">
    <div class="red-legend"></div>@lang("غير مستمر")<br />
    <div class="green-legend"></div>@lang("مستمر")<br />
    <div class="blue-legend"></div>@lang("حجز قاعة")
  </div>
  <h6 class="mb-sm-auto mb-0 mt-4">@lang('يمكن تحريك المقبض الأصفر لتغير حجم الخانات.')</h6>
</div>
<div class="lds-dual-ring"></div>
<div id="calendar" class="hide-calendar"></div>