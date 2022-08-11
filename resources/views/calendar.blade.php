@extends("master.authenticated", ["user" => $user])

@section("title", "الرزنامة")

@push("stylesheets")
  <link href='https://use.fontawesome.com/releases/v5.0.6/css/all.css' rel='stylesheet'>
  <link href="/css/calendar.css?v=8" rel="stylesheet" />
  <link href="/css/scheduler-core.min.css" rel="stylesheet"/>
  <link href="/css/scheduler-bootstrap.min.css" rel="stylesheet" />
  <link href="/css/scheduler-resource-timeline.min.css" rel="stylesheet" />
  <link href="/css/scheduler-timeline.min.css" rel="stylesheet" />
@endpush

@section('contents')
  @component('components.full-card')
    @slot('cardTitle')
      <div class="text-center">
        @lang("الرزنامة")
      </div>
    @endslot

    @component('components.calendar', ["date" => $date])
    @endcomponent
  @endcomponent
@endsection

@push('scripts')
  <script src="/js/scheduler-core.min.js"></script>
  <script src="/js/scheduler-bootstrap.min.js"></script>
  <script src="/js/scheduler-interaction.min.js"></script>
  <script src="/js/scheduler-timeline.min.js"></script>
  <script src="/js/scheduler-resource-common.min.js"></script>
  <script src="/js/scheduler-resource-timeline.min.js"></script>
  <script src="/js/calendar.js?v=8"></script>
@endpush
