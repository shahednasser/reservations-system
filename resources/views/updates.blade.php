@extends('master.authenticated', ["user" => $user])

@section('title', __('تعديلات البرنامج'))

@section('contents')
  @component('components.full-card')
    @slot('cardTitle')
      @lang('تعديلات البرنامج')
    @endSlot

    <ol>
      <li>
        @lang('تم تغيير طريقة إضافة الوقت.')
      </li>
    </ol>
  @endcomponent
@endsection
