@extends("master.authenticated", ["user" => $user])
@php
    $title = __('تعديل شامل - جميع الحجوزات')
@endphp
@section("title", $title)

@push('stylesheets')
    <link rel="stylesheet" href="/css/iziModal.min.css">
@endpush

@section('contents')
    @component('components.full-card')
        @slot('cardTitle')
            {{$title}}
            @if($reservations->count())
                <select class="form-control" name="filter">
                    <option value="all">@lang("جميع الطلبات")</option>
                    <option value="long">@lang("الحجوزات المستمرة")</option>
                    <option value="temp">@lang("الحجوزات غير المستمرة")</option>
                    <option value="manual">@lang("حجوزات القاعة")</option>
                </select>
            @endif
        @endslot

        @if(!$reservations)
            <p class="lead">لا يوجد أي حجوزات</p>
        @else
            <form method="post" id="reservationsForm">
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
                @csrf
                @component('components.reservations', ["reservations" => $reservations, 'status' => "mass-editing",
                                                    "paginated" => false])
                @endcomponent
            </form>
        @endif
    @endcomponent
@endsection

@section('modals')
    <div class="iziModal pr-1 pl-1" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalTitle"
         aria-hidden="true">
        <div class="modal-header">
            <h5 class="modal-title" id="rejectModalTitle">@lang("تأكيد الإلغاء")</h5>
        </div>
        <div class="pt-3 pb-3">
            @lang("هل أنت متأكد من إلغاء الطلب؟")
        </div>
        <div class="modal-footer">
            <a class="btn btn-danger text-white mass-action" role="button" id="massReject">@lang("نعم")</a>
            <button class="btn btn-secondary" type="button" data-izimodal-close="">@lang("لا")</button>
        </div>
    </div>
    <div class="iziModal pr-1 pl-1" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle"
         aria-hidden="true">
        <div class="modal-header">
            <h5 class="modal-title" id="deleteModalTitle">@lang("تأكيد الحذف")</h5>
        </div>
        <div class="pt-3 pb-3">
            @lang("هل أنت متأكد من حذف الطلب؟")
        </div>
        <div class="modal-footer">
            <a class="btn btn-danger text-white mass-action" role="button" id="massDelete">@lang("نعم")</a>
            <button class="btn btn-secondary" type="button" data-izimodal-close="">@lang("لا")</button>
        </div>
    </div>
    <div class="iziModal pr-1 pl-1" id="deletePauseModal" tabindex="-1" role="dialog" aria-labelledby="deletePausedModalTitle"
        aria-hidden="true">
        <div class="modal-header">
            <h5 class="modal-title" id="deletePausedModalTitle">@lang("تأكيد الحذف")</h5>
        </div>
        <div class="pt-3 pb-3">
            @lang("هل أنت متأكد من إلغاء التوقيف المؤقت لهذه الحجوزات؟")
        </div>
        <div class="modal-footer">
            <a class="btn btn-danger text-white mass-action" role="button" id="massDeletePaused">@lang("نعم")</a>
            <button class="btn btn-secondary" type="button" data-izimodal-close="">@lang("لا")</button>
        </div>
    </div>
    @component('components.pause-reservation-modal', ["floors" => $floors, "rooms" => $rooms])
    @endcomponent
@endsection

@push('scripts')
    <script src="/js/search-reservations.js?v=8"></script>
    <script src="/js/reservations.js?v=8"></script>
    <script src="/js/iziModal.min.js?v=8" type="text/javascript"></script>
    <script src="/js/mass-editing.js?v=8" type="text/javascript"></script>
@endpush