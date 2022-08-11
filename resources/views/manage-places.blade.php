@extends("master.authenticated", ["user" => $user])
@php
  $title = __('إدارة الأماكن')
@endphp
@section("title", $title)

@push('stylesheets')
  <link rel="stylesheet" href="/css/iziModal.min.css">
@endpush

@section('contents')
  @component('components.full-card')
    @slot('cardTitle')
      {{$title}}
      <a href="/add-floor" class="btn btn-outline-success ml-md-2">@lang("أضف مكان")</a>
    @endslot

    <div class="accordion" id="placesAccordion">
      @foreach ($floors as $floor)
        <div class="card">
          <div class="card-header bg-success d-flex justify-content-between" id="heading{{$floor->id}}">
            <h5 class="mb-0">
              @if($floor->number_of_rooms)
                <button class="btn btn-link text-white" type="button" data-toggle="collapse" data-target="#collapse{{$floor->id}}"
                  aria-expanded="true" aria-controls="collapse{{$floor->id}}">
                  <u>{{$floor->name}}</u>
                </button>
              @else
                <span class="btn accordion-header">{{$floor->name}}</span>
              @endif
            </h5>
            <div class="links align-self-center">
              <a href="/edit-floor/{{$floor->id}}" class="text-white"><u>@lang("تعديل")</u></a>
              <button class="btn btn-link text-white delete-btn delete-floor-btn" data-izimodal-open="#deleteModal"
              data-izimodal-transitionin="fadeInDown" id="{{$floor->id}}"
              style="font-size: 1rem !important; vertical-align: initial !important;">
                <u>@lang("حذف")</u></button>
            </div>
          </div>
          @if($floor->number_of_rooms)
            <div id="collapse{{$floor->id}}" class="collapse" aria-labelledby="heading{{$floor->id}}"
              data-parent="#placesAccordion">
              <div class="card-body">
                <ul>
                  @foreach($floor->rooms()->get() as $room)
                    <li>
                      {{$room->room_number != -1 ? $room->room_number : ''}} {{$room->name ? ($room->room_number != -1 ?
                        '('.$room->name.')' : $room->name) : '' }}
                    </li>
                  @endforeach
                </ul>
              </div>
            </div>
          @endif
        </div>
      @endforeach
    </div>
  @endcomponent
@endsection

@section('modals')
  <div class="iziModal pl-1 pr-1" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle"
    aria-hidden="true">
    <div class="modal-header">
      <h5 class="modal-title" id="deleteModalTitle">@lang("تأكيد الحذف")</h5>
    </div>
    <div class="pt-3 pb-3">
      @lang("هل أنت متأكد من حذف المكان؟ سيتم حذف جميع الحجوزات المتعلقة بهذا المكان.")
    </div>
    <div class="modal-footer">
      <button class="btn btn-danger" type="button">@lang("نعم")</button>
      <button class="btn btn-secondary" type="button" data-izimodal-close="">@lang("كلا")</button>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="/js/iziModal.min.js" type="text/javascript"></script>
  <script src="/js/manage-places.js?v=8"></script>
@endpush
