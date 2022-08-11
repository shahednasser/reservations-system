<input type="text" name={{$name}} class="input-time form-control" value="{{$value ? $value : "00:00"}}"
{{$isDisabled ? 'disabled' : ''}} />
<div class="input-time-inline d-none {{$isDisabled ? 'disabled' : ''}}" />
