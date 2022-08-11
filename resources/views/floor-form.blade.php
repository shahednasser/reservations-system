@extends("master.authenticated", ["user" => $user])
@php
  $title = $floor ? __('تعديل المكان') : __('إضافة مكان')
@endphp
@section("title", $title)

@section('contents')
  @component('components.full-card')
    @slot('cardTitle')
      <div class="text-center">
        {{$title}}
      </div>
    @endslot

    <form action="{{$floor ? '/edit-floor/'.$floor->id : '/add-floor'}}" method="post"
          class="mr-md-2 ml-md-2">
      @csrf
      <div class="form-group row">
        <label for="name" class="col-sm-2 col-form-label">@lang("الإسم")</label>
        <div class="col-sm-10">
          <input type='text' name="name" value="{{$floor ? $floor->name : ''}}" class="form-control" required />
        </div>
      </div>
      <fieldset class="form-group">
        <div class="row">
          <legend class="col-sm-2 col-form-label pt-0">
            @lang("الغرف")
          </legend>
          <div class="col-sm-10" id="roomsDiv">
            <div>
              <button class="btn btn-info" id="addRoomsBtn" type="button">
                @lang("أضف غرفة")
              </button>
            </div>
            @if($floor)
              @php
                $i = 1;
              @endphp
              @foreach($floor->rooms()->get() as $room)
                <div class="row room-row">
                  <div class="col-6">
                    <div class="form-group">
                      <label for="room_name_{{$i}}">@lang("الإسم")</label>
                      <input name="room_name_{{$i}}" type="text" class="form-control" value="{{$room->name}}" />
                      <input name="room_{{$i}}_id" type="hidden" value="{{$room->id}}" />
                    </div>
                  </div>
                  <div class="col-5">
                    <div class="form-group">
                      <label for="room_number_{{$i}}">@lang('رقم الغرفة')</label>
                      <input name="room_number_{{$i}}" type="number"
                        class="form-control" value="{{$room->room_number && $room->room_number != -1 ? $room->room_number : ''}}" />
                      <input name="room_{{$i}}_id" type="hidden" value="{{$room->id}}" />
                    </div>
                  </div>
                  <div class="col-1 align-self-center cursor-pointer">
                    <img src="/assets/trash.svg" class="delete-icon" />
                  </div>
                </div>
                @php
                  $i++;
                @endphp
              @endforeach
            @endif
          </div>
        </div>
      </fieldset>
      <div class="text-center">
        <button class="btn btn-success" type="submit">{{$floor ? __('عدل') : __('أضف')}}</button>
        <a href="{{url()->previous()}}" role="button" class="btn btn-secondary">
          @lang("إلغاء")
        </a>
      </div>
    </form>
  @endcomponent
@endsection

@push('scripts')
  <script src="/js/floor-form.js?v=8"></script>
@endpush
