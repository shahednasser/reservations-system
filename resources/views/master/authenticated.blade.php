@extends("master.master")

@push("stylesheets")
  <link href="/css/animate.css" rel="stylesheet" />
  <link href="/css/navbar.css?v=8" rel="stylesheet" />
  <link href="/css/picker.min.css" rel="stylesheet"  />
  <link href="/css/jquery-ui.min.css" rel="stylesheet" />
  <link href="/css/jquery-ui.structure.min.css" rel="stylesheet" />
  <link href="/css/jquery-ui.theme.min.css" rel="stylesheet" />
@endpush

@push('head-additional')
  <link rel="manifest" href="/manifest.json" />
  <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async=""></script>
  <script>
    var OneSignal = window.OneSignal || [];
    OneSignal.push(function() {
      OneSignal.init({
        appId: "d41e104f-303f-4a0d-90a4-fd6bd543b4d7",
      });
    });
  </script>
@endpush

@section('content')
  <nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top">
    <button class="navbar-toggler" type="button" data-toggle="collapse"
        data-target="#navbarCollapsable" aria-controls="navbarCollapsable"
        aria-expanded="false" aria-label="Menu">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class='collapse navbar-collapse' id="navbarCollapsable">
      <ul class="navbar-nav ml-auto pr-0">
        <li class="nav-item">
          <a href="/calendar" class="nav-link">{{__("الرزنامة")}}</a>
        </li>
        <li class="nav-item">
          <a href="/weekly-calendar" class="nav-link">{{__("الرزنامة الاسبوعية")}}</a>
        </li>
        @if($user->isAdmin())
          <li class="nav-item">
            <a href="/new-reservations" class="nav-link">@lang('طلبات الحجوزات')
              <span class="badge badge-pill badge-light">{{$requests_count}}</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="/all-reservations" class="nav-link">@lang('جميع الحجوزات')</a>
          </li>
        @else
          <li class="nav-item">
            <a href="/my-reservations" class="nav-link">@lang("حجوزاتي")</a>
          </li>
        @endif
        <li class="nav-item dropdown">
          <a href="#" class="nav-link dropdown-toggle" id="addReservationDropdown"
            role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            {{__("أضف حجز")}}
          </a>
          <div class="dropdown-menu">
            <a class='dropdown-item' href="/add-reservation/long">@lang("حجز مستمر")</a>
            <a class="dropdown-item" href="/add-reservation/temporary">@lang("حجز غير مستمر")</a>
            @if($user->isAdmin())
              <a class="dropdown-item" href="/admin-add-reservation">@lang("حجز قاعة")</a>
            @endif
          </div>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="settingsDropdown"
              role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              الاعدادات
          </a>
          <div class="dropdown-menu" aria-labelledby="settingsDropdown">
            <a class="dropdown-item" href="/view-account">@lang('حسابي')</a>
            @if($user->isAdmin())
              <a class="dropdown-item" href="/view-users">@lang('جميع الحسابات')</a>
              <a class="dropdown-item" href="/manage-places">@lang('إدارة الأماكن')</a>
            @endif
            <a class="dropdown-item" href="/help">@lang('مساعدة')</a>
            <a class="dropdown-item" href="/logout">@lang('تسجيل الخروج')</a>
          </div>
        </li>
      </ul>
      <span class="my-lg-0 ml-lg-2">
        <span class="user-info d-lg-none d-block position-relative">
          <span class="user-info-icon">
            <img src="/assets/user.svg" class="img-fluid" />
          </span>
          <a href="/view-account" class="stretched-link">{{$user->username}}</a>
        </span>
        <form class="search-form" action="/search" method="get">
          @csrf
          <input type='search' name="search" class="underlined-textbox" required />
          <button type="submit" class="icon-button">
            <span class="icon-button">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FFF"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search">
              <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </span>
          </button>
        </form>
        <span class="user-info mr-2 position-relative d-lg-inline-block d-none" data-toggle="tooltip" data-placement="bottom" title="{{$user->username}}">
          <span class="user-info-icon">
            <img src="/assets/user.svg" class="img-fluid" />
          </span>
          <a href="/view-account" class="stretched-link"></a>
        </span>
      </span>
    </div>
    <div class="position-relative notification-container">
      <img src="/assets/notifications_none.png" class="icon-button" id="notificationsBtn" />
      <span class="red-dot d-none"></span>
    </div>
  </nav>
  <span id="id" class="d-none">{{$user->id}}</span>
  <div class="pl-3" style="position:relative;">
    <div class="sidebar animated slideOutLeft d-none">
    </div>
    @if(\Session::has('message'))
      <div class="mb-0 alert alert-{{\Session::get('message_class')}}">
        {{\Session::get("message")}}
      </div>
    @endif
    <div id="sound">

    </div>
    @yield("contents")
  </div>
@endsection

@push('scripts')
  <script src="/js/navbar.js?v=8"></script>
  <script src="/js/howler.min.js"></script>
  <script src="/js/bootstrap.min.js"></script>
  <script src="/js/app.js"></script>
  <script src="/js/moment.js"></script>
  <script src="/js/global.js?v=8"></script>
  <script src="/js/print.min.js"></script>
  <script src="/js/picker.min.js"></script>
  <script src="/js/time-input.js?v=8"></script>
  <script src="/js/jquery-ui.min.js"></script>
  <script src="/js/jquery.ui.datepicker-ar.js"></script>
  <script src="/js/date-picker.js?v=8"></script>
@endpush
