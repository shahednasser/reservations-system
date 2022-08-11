@extends('master.authenticated', ["user" => $user])

@php
  $title = __('نتائج البحث ل').$search;
@endphp

@section('title', $title)

@section('contents')
  @component('components.full-card')
    @slot("cardTitle")
      <div class="text-center">
        {{$title}}
      </div>
    @endslot
    @php
      $first = true;
    @endphp
    @if($results->count())
      <div class="search-results">
        @foreach($results as $result)
          @php
            if(!$first){
              echo '<hr />';
            }
            else{
              $first = false;
            }
            echo '<h5>';
            $link = "";
            $result_class = get_class($result);
            switch($result_class){
              case "App\Reservation":
                if($user->isAdmin()){
                  $link .= '<a href="/view-reservation/'.$result->id.'">'.$result->event_name.'</a> - ';
                } else {
                  $link .= '<a href="/show-reservation/'.$result->id.'">'.$result->event_name.'</a> - ';
                }
                if($result->longReservation){
                  $link .= __('حجز مستمر');
                }
                else{
                  $link .= __('حجز غير مستمر');
                }
                $status = getFullStatus($result);
                $link .= '<span class="mr-5 mt-md-0 mt-2 d-md-inline d-inline-block status '. $status[0] .'">'.$status[1].'</span>';
                break;
              case "App\ManualReservation":
                $link .= '<a href="/view-admin-reservation/'.$result->id.'">'.$result->event_type.'</a> - '.__("حجز قاعة");
                $status = getFullStatus($result);
                $link .= '<span class="mr-5 mt-md-0 mt-2 d-md-inline d-inline-block status '. $status[0] .'">'.$status[1].'</span>';
                break;
              case 'App\User':
                if($user->isAdmin() || $result->id === $user->id){
                  $link .= '<a href="/view-account/'.$result->id.'">'.$result->username.'</a> - '.__("مستخدم");
                }
                break;
            }
            echo $link.'</h5>';
          @endphp
        @endforeach
      </div>
    @else
      <p class="lead">
        @lang("لا يوجد أي نتائج")
      </p>
    @endif
  @endcomponent
@endsection
