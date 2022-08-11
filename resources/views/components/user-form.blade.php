<form method="post" action="{{$action}}">
  @csrf
  <div class="form-group row">
    <label for="name" class="col-sm-2 col-form-label">@lang('الإسم')</label>
    <div class="col-sm-10">
      <input name="name" type="text" class="form-control " value="{{old('name') ? old('name') :
        ($user_account ? $user_account->name : '')}}" placeholder="@lang('الإسم')" required />
    </div>
  </div>
  <div class="form-group row">
    <label for="username" class="col-sm-2 col-form-label">@lang('إسم المستخدم')</label>
    <div class="col-sm-10">
      <input name="username" type="text" class="form-control " value="{{old('username') ? old('username') :
        ($user_account ? $user_account->username : '')}}" placeholder="@lang('إسم المستخدم')" required />
    </div>
  </div>
  <div class="form-group row">
    <label for="position" class="col-sm-2 col-form-label">@lang('صفته')</label>
    <div class="col-sm-10">
      <input name="position" type="text" class="form-control " value="{{old('position') ? old('position') :
        ($user_account ? $user_account->position : '')}}" placeholder="@lang('صفته')" />
    </div>
  </div>
  <div class="form-group row">
    <label for="password" class="col-sm-2 col-form-label">@lang("كلمة المرور")</label>
    <div class="col-sm-10">
      <input name="password" type="password" class="form-control" placeholder="@lang("كلمة المرور")"
      {{!$is_editing ? 'required' : ''}} />
      @if($is_editing)
        <small class="form-text text-muted mb-2">@lang("إملأ هذا الحقل فقط إن كنت تريد تغير كلمة المرور للمستخدم")</small>
      @endif
      <input name="password_confirmation" type="password" class="form-control{{!$is_editing ? ' mt-3' : ''}}" placeholder="@lang('تأكيد كلمة المرور')"
      {{!$is_editing ? 'required' : ''}} />
    </div>
  </div>
  <div class="form-group row">
    <label for="is_admin" class="col-sm-2 col-form-label">@lang('مشرف')</label>
    <div class="col-sm-10">
      <input name="is_admin" type="checkbox" class="form-control " {{old('is_admin') ? 'checked' :
        ($user_account && $user_account->is_admin ? 'checked' : '')}} style="width: 2em;" />
    </div>
  </div>
  <div class="form-group row">
    <label for="is_admin" class="col-sm-2 col-form-label">@lang('موظف أمانة المبنى')</label>
    <div class="col-sm-10">
      <input name="is_maintainer" type="checkbox" class="form-control " {{old('is_maintainer') ? 'checked' :
        ($user_account && $user_account->is_maintainer ? 'checked' : '')}} style="width: 2em;" />
    </div>
  </div>
  <div class="text-center">
    <button class="btn btn-success" type="submit">@lang($is_editing ? "عدل" : "أضف")</button>
    <a class="btn btn-secondary" role="button" href="{{url()->previous()}}">@lang("العودة")</a>
  </div>
</form>
