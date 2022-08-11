@extends('master.authenticated', ["user" => $user])

@php
  $title = 'حساب '.$user_account->name;
@endphp

@section('title', $title)

@push("stylesheets")
  <link href="/css/user.css?v=8" rel="stylesheet" />
  <link rel="stylesheet" href="/css/iziModal.min.css">
@endpush

@section('contents')
  @component('components.full-card')
    @slot('cardTitle')
      @lang($title)
    @endslot

    <div class="user-info">
      <div class="row">
        <div class="col-sm-6 col-12">
          <strong>@lang('الإسم'): </strong>
        </div>
        <div class="col-sm-6 col-12">
          {{$user_account->name}}
        </div>
      </div>
      <div class='row'>
        <div class="col-sm-6 col-12">
          <strong>@lang("إسم المستخدم"): </strong>
        </div>
        <div class="col-sm-6 col-12">
          {{$user_account->username}}
        </div>
      </div>
      <div class="row">
        <div class="col-sm-6 col-12">
          <strong>@lang("صفته"): </strong>
        </div>
        <div class="col-sm-6 col-12">
          {{$user_account->position}}
        </div>
      </div>
      <div class="row">
        <div class="col-sm-6 col-12">
          <strong>@lang("مشرف"): </strong>
        </div>
        <div class="col-sm-6 col-12">
          {{$user_account->is_admin ? __('نعم') : __('لا')}}
        </div>
      </div>
      <div class="row">
        <div class="col-sm-6 col-12">
          <strong>@lang("موظف أمانة المبنى"): </strong>
        </div>
        <div class="col-sm-6 col-12">
          {{$user_account->is_maintainer ? __('نعم') : __('لا')}}
        </div>
      </div>
      @if($user->isAdmin())
        <div class="text-center">
          <a class="btn btn-success" href="/edit-account/{{$user_account->id}}" />
            @lang("تعديل")
          </a>
          <button class="btn btn-danger" type="button" data-izimodal-open="#deleteModal"
          data-izimodal-transitionin="fadeInDown">
            @lang("حذف")
          </button>
        </div>
      @endif
    </div>
  @endcomponent
@endsection

@section("modals")
  @if($user->isAdmin())
    <div class="iziModal pr-1 pl-1" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle"
      aria-hidden="true">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalTitle">@lang("تأكيد الحذف")</h5>
      </div>
      <div class="pt-3 pb-3">
        @lang("هل أنت متأكد من حذف هذا الحساب")
      </div>
      <div class="modal-footer">
        <a class="btn btn-danger" role="button" href="/delete-user/{{$user_account->id}}">@lang("نعم")</a>
        <button class="btn btn-secondary" type="button" data-izimodal-close="">@lang("كلا")</button>
      </div>
    </div>
  @endif
@endsection

@push('scripts')
  <script src="/js/iziModal.min.js" type="text/javascript"></script>
  <script src="/js/startModal.js?v=8" type="text/javascript"></script>
@endpush
