@extends('master.authenticated', ["user" => $user])

@php
  $title = __("حجوزاتي");
@endphp
@section('title')
  {{$title}}
@endsection

@section("contents")
  @component('components.full-card')
    @slot("cardTitle")
      {{$title}}
    @endslot
    @if($reservations->count())
      @component('components.reservations', ["reservations" => $reservations, 'status' => 'my-reservations',
      'paginated' => true])
      @endcomponent
    @else
      <p class="lead">@lang("لا يوجد أي حجوزات.")</p>
    @endif
  @endcomponent
@endsection
