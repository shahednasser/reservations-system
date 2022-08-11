<input type="date" {{isset($id) ? 'id='.$id : ''}} value="{{$value ?: date("Y-m-d")}}"
       class="d-none" name="{{$name}}" />
<input type="text" class="form-control date-picker{{isset($classes) ? ' '.$classes : ''}}"
       value="{{date_format(date_create($value), "d/m/Y")}}"
        {{$isDisabled ? 'disabled' : ''}} {{$isRequired ? 'required' : ''}}>