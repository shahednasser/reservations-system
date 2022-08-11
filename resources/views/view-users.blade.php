@extends('master.authenticated', ["user" => $user])

@php
  $title = __('جميع الحسابات');
@endphp

@section('title', $title)

@section('contents')
  @component('components.full-card')
    @slot('cardTitle')
      {{$title}}
      <a class="btn btn-success" role="button" href="/add-user">@lang("أضف مستخدم")</a>
    @endslot

    <table class="table reservations-table">
      <thead>
        <tr>
          <th>
            @lang("إالإسم")
          </th>
          <th>
            @lang("إسم المستخدم")
          </th>
          <th>

          </th>
        </tr>
      </thead>
      <tbody>
        @foreach($users as $u)
          <tr>
            <td>
              {{$u->name}}
            </td>
            <td>
              {{$u->username}}
            </td>
            <td>
              <a href="/view-account/{{$u->id}}">@lang("المزيد")</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    {{$users->links()}}
  @endcomponent
@endsection
