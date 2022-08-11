@extends("master.authenticated", ["user" => $user])
@php
  $title = $status == "new" ? __("طلبات الحجوزات") : __("جميع الحجوزات");
@endphp
@section("title", $title)

@section('contents')
  @component('components.full-card')
    @slot('cardTitle')
      @lang($title)
      @if($reservations->count())
        <select class="form-control" name="filter">
          <option value="all">@lang("جميع الطلبات")</option>
          <option value="long">@lang("الحجوزات المستمرة")</option>
          <option value="temp">@lang("الحجوزات غير المستمرة")</option>
          @if($status == "all")
            <option value="manual">@lang("حجوزات القاعة")</option>
          @endif
        </select>
        @if($status === "all")
          <a role="button" class="btn btn-primary" href="/mass-editing">@lang('تعديل شامل')</a>
        @endif
      @endif
    @endslot
    @if($reservations->count())
      @component('components.reservations', ["reservations" => $reservations, 'status' => $status, 'paginated' => true])
      @endcomponent
    @else
      <p class="lead">لا يوجد أي طلبات.</p>
    @endif
  @endcomponent
@endsection

@push('scripts')
  <script src="/js/reservations.js?v=8"></script>
  <script src="/js/search-reservations.js?v=8"></script>
@endpush
