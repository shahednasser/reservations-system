@extends("master.authenticated", ["user" => $user])

@section("title", "الصفحة الرئيسية")

@push("stylesheets")
    <link href='https://use.fontawesome.com/releases/v5.0.6/css/all.css' rel='stylesheet'>
    <link href="/css/calendar.css" rel="stylesheet" />
    <link href="/css/scheduler-core.min.css" rel="stylesheet"/>
    <link href="/css/scheduler-bootstrap.min.css" rel="stylesheet" />
    <link href="/css/scheduler-resource-timeline.min.css" rel="stylesheet" />
    <link href="/css/scheduler-timeline.min.css" rel="stylesheet" />
    <link href="/css/home.css" rel="stylesheet" />
@endpush

@section('contents')
  <section>
    @component("components.full-card")
      @slot('cardTitle')
        {{__("رزنامة اليوم")}}
      @endslot


      @component('components.calendar', ["date" => date_create()])

      @endcomponent
    @endcomponent
    @if($user->isAdmin())
      @component("components.card")
        @slot("cardTitle")
          {{__('طلبات حجوزات جديدة')}}
          <a class="btn btn-outline-success ml-md-2 mt-2" href="/new-reservations">@lang('جميع الطلبات')</a>
        @endslot

        @component('components.reservations', ["reservations" => $new_reservations, 'status' => 'new', 'paginated' => false])
        @endcomponent

      @endcomponent
    @endif
  </section>
@endsection

@push('scripts')
  <script src="/js/search-reservations.js?v=8"></script>
  <script src="/js/jquery.scrollTo.min.js"></script>
  <script src="/js/scheduler-core.min.js"></script>
  <script src="/js/scheduler-bootstrap.min.js"></script>
  <script src="/js/scheduler-interaction.min.js"></script>
  <script src="/js/scheduler-timeline.min.js"></script>
  <script src="/js/scheduler-resource-common.min.js"></script>
  <script src="/js/scheduler-resource-timeline.min.js"></script>
  <script src="/js/calendar.js?v=8"></script>
@endpush
