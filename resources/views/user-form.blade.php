@extends('master.authenticated', ["user" => $user])

@php
  $title = $is_editing ? __('تعديل الحساب') : __('إضافة حساب');
@endphp

@section('title', $title)

@section('contents')
  @component('components.full-card')
    @slot('cardTitle')
      {{$title}}
    @endslot

    @if($errors->count())
      <div class="alert alert-danger">
        <ul>
          @foreach ($errors->getMessages() as $value)
            @foreach($value as $message)
              <li>{{$message}}</li>
            @endforeach
          @endforeach
        </ul>
      </div>
    @endif

    @component('components.user-form', ["user_account" => $user_account, "action" => $is_editing ? '/edit-user/'.$user_account->id :
                                        '/add-user', 'is_editing' => $is_editing])
    @endcomponent
  @endcomponent
@endsection
