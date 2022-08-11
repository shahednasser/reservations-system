@php
  $user = Auth::user();
  $title = __('حدث خطأ');
@endphp

@extends($user ? 'master.authenticated' : 'master.master', $user ? ["user" => $user] : [])

@section($user ? 'contents' : 'content')
  @component('components.full-card')
    @slot('cardTitle')
      {{$title}}
    @endslot

    @lang('حدث خطأ. الرجاء ابلاغ الجهة المسؤولة وإعادة المحاولة لاحقاً.')
  @endcomponent
@endsection
