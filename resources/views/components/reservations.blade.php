@if($reservations->count() > 0)
    @if($status === "mass-editing")
        <div class="btn-group mass-editing-toolbar" role="group" aria-label="الأدوات">
            <a href="#" role="button" class="btn btn-danger"
               data-izimodal-open="#deleteModal" data-izimodal-transitionin="fadeInDown">@lang('حذف')</a>
            <a href="#" role="button" class="btn royal-purple-button"
               data-izimodal-open="#rejectModal" data-izimodal-transitionin="fadeInDown">@lang('إلغاء')</a>
            <a href="#" role="button" class="btn btn-secondary"
               data-izimodal-open="#pauseModal" data-izimodal-transitionin="fadeInDown">@lang('توقيف لوقت مؤقت')</a>
            <a href="#" role="button" class="btn btn-info"
               data-izimodal-open="#deletePauseModal" data-izimodal-transitionin="fadeInDown">@lang('إلغاء التوقيف لوقت مؤقت')</a>
        </div>
    @endif
  <input type="search" name="search" placeholder="{{__('إبحث')}}" class="form-control" />
  <table class="table reservations-table">
    <thead>
        @if($status === "mass-editing")
            <tr>
                <td><input id="checkAll" type="checkbox" /></td>
            </tr>
        @endif
      <tr>
          @if($status === "mass-editing")
            <th></th>
          @endif
        <th>@lang('مقدم الطلب')</th>
        <th>@lang('من')</th>
        <th>@lang('عنوان النشاط')</th>
        <th>@lang('نوع النشاط')</th>
        @if($status == "all" || $status == "mass-editing" || $status === "my-reservations")
          <th>@lang('الحالة')</th>
        @endif
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach($reservations as $reservation)
        @php
            $isManual = get_class($reservation) == "App\ManualReservation";
            $type = $isManual ? 'manual' : ($reservation->longReservation ? 'long' : 'temp');
        @endphp
        <tr class="{{$type}}">
            @if($status === "mass-editing")
                <td>
                    <input type="checkbox" name="{{$type.'_'.$reservation->id}}" class="reservation-input" />
                </td>
            @endif
            @if(!$isManual)
            <td>{{$reservation->user()->withTrashed()->first()->name}}</td>
            <td>{{$reservation->committee}}</td>
            <td>{{$reservation->event_name}}</td>
            <td>{{$reservation->longReservation ? __('مستمر') : __('غير مستمر') }}</td>
            @if($status == "all" || $status == "mass-editing" || $status === "my-reservations")
              @php
                $stat = getFullStatus($reservation);
              @endphp
              <td><span class="status {{$stat[0]}}">{{$stat[1]}}</span></td>
            @endif
            <td>
              @if($status == "new")
                <a href="/reservation/{{$reservation->id}}">@lang('المزيد')</a>
              @elseif($status === "my-reservations")
                  <a href="/show-reservation/{{$reservation->id}}">@lang("المزيد")</a>
              @else
                @if($stat[0] == "status-normal")
                  <a href="/reservation/{{$reservation->id}}">@lang('المزيد')</a>
                @else
                  <a href="/view-reservation/{{$reservation->id}}">@lang('المزيد')</a>
                @endif
              @endif
            </td>
          @else
            <td>{{$reservation->full_name}}</td>
            <td>{{$reservation->organization ? $reservation->organization : ''}}</td>
            <td>{{$reservation->event_type}}</td>
            <td>حجز قاعة</td>
            @if($status == "all" || $status == "mass-editing" || $status === "my-reservations")
                @php
                    $stat = getFullStatus($reservation);
                @endphp
                <td><span class="status {{$stat[0]}}">{{$stat[1]}}</span></td>
            @endif
            <td>
              <a href="/view-admin-reservation/{{$reservation->id}}">@lang('المزيد')</a>
            </td>
          @endif
        </tr>
      @endforeach
    </tbody>
  </table>
  @if($paginated)
    {{$reservations->links()}}
  @endif
@else
  <div class="m-3">
    @lang("لا يوجد أي حجوزات.")
  </div>
@endif
