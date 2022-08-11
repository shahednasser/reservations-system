@extends('master.authenticated', ["user" => $user])

@php
  $title = __("تعديل نشاط");
@endphp



@section("title", $title)

@section("contents")
  @component('components.full-card')
    @slot('cardTitle')
      {{$title}}
    @endslot
    @if($errors->count())
      <div class="alert alert-danger">
        <ul>
          @foreach ($errors->getMessages() as $value)
            @foreach($value as $message)
              <li>{{$message}}</li>
            @endforeach
          @endforeach
        </ul>
      </div>
    @endif
    <form action="/edit-reservation/{{$reservation->id}}" method="post" class="edit-form">
      @csrf
      <div class="row">
        <div class="col-lg-6 ol-md-6 col-12">
          <div class="form-group mb-5">
            <label for="committee">{{__("اللجنة")}}</label>
            <input type="text" class="form-control" name="committee" value="{{old('committee') ?: $reservation->committee}}"
              required />
          </div>
        </div>
        <div class="col-lg-6 ol-md-6 col-12">
          <div class="form-group mb-5">
            <label for="event_name">{{$reservation->longReservation ? __("نوع النشاط") : __('عنوان النشاط')}}</label>
            <input type="text" class="form-control" name="event_name" value="{{old('event_name') ?: $reservation->event_name}}"
              required />
          </div>
        </div>
      </div>
      @if($reservation->longReservation)
        @component('components.edit-long-reservation', ["reservation" => $reservation,
                        'floors' => $floors, 'rooms' => $rooms])
        @endcomponent
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label for="notes">@lang("ملاحظات")</label>
              <textarea name="notes" class="form-control">{{old('notes') ?: $reservation->notes}}</textarea>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label for="supervisors">@lang("الأساتذة المشرفون")</label>
              <textarea name="supervisors" class="form-control">{{old('supervisors') ?: $reservation->supervisors}}</textarea>
            </div>
          </div>
        </div>
      @else
        @component('components.edit-temporary-reservation', ["reservation" => $reservation,
                        'floors' => $floors, 'rooms' => $rooms])
        @endcomponent
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label for="supervisors">@lang("المشرفون أثناء النشاط")</label>
              <textarea name="supervisors" class="form-control">{{old('supervisors') ?: $reservation->supervisors}}</textarea>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <label for="equipment_needed">@lang("المستلزمات المطلوبة")</label>
            <div class="row mb-2">
              <div class="col-12">
                <input type="text" name="equipment_needed[]" class="form-control"
                  value="{{old('equipment_needed') && isset(old('equipment_needed')[0]) ?
                            old('equipment_needed')[0] : ($reservation->temporaryReservation->equipment_needed_1)}}" />
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-12">
                <input type="text" name="equipment_needed[]" class="form-control"
                  value="{{old('equipment_needed') && isset(old('equipment_needed')[1]) ?
                            old('equipment_needed')[1] : ($reservation->temporaryReservation->equipment_needed_2)}}" />
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-12">
                <input type="text" name="equipment_needed[]" class="form-control"
                  value="{{old('equipment_needed') && isset(old('equipment_needed')[2]) ?
                            old('equipment_needed')[2] : ($reservation->temporaryReservation->equipment_needed_3)}}" />
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label for="notes">@lang("ملاحظات إضافية")</label>
              <textarea name="notes" class="form-control">{{old('notes') ?: $reservation->notes}}</textarea>
            </div>
          </div>
        </div>
      @endif
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
        @component('components.buttonWithLoader',
                    ["classes" => 'btn btn-info mb-2',
                    "id" => $reservation->temporaryReservation ? 'tempRes' : '',
                    'text' => __('تأكد'), 'name' => 'checkReservations',
                    'theme' => 'dark'])
        @endcomponent
      </div>
      <div id="result" class="mt-2"></div>
      <div class="row">
        <div class="col-12 text-left">
          <button type="submit" class="btn btn-success" disabled>@lang("تعديل")</button>
          <a href="/show-reservation/{{$reservation->id}}" data-role="button" class="btn btn-secondary">
            @lang("إلغاء التعديل")
          </a>
        </div>
      </div>
    </form>
  @endcomponent
@endsection

@push("scripts")
  <script src="/js/iziModal.min.js" type="text/javascript"></script>
  <script src="/js/edit-reservation.js?v=8"></script>
  <script src="/js/reservation.js?v=8"></script>
@endpush
