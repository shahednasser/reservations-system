@php
  $user = Auth::user();
  $title = __('الصفحة غير موجودة');
@endphp

@extends($user ? 'master.authenticated' : 'master.master', $user ? ["user" => $user] : [])

@section($user ? 'contents' : 'content')
  @component('components.full-card')
    @slot('cardTitle')
      {{$title}}
    @endslot

    @lang('الصفحة التي تبحث عنها غير موجودة')
  @endcomponent
@endsection
