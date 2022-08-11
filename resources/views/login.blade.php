@extends("master.master")

@section("title", "تسجيل الدخول")

@push("stylesheets")
  <link href="/css/login.css?v=8" rel="stylesheet" />
@endpush

@section("content")
<div class="container">
  <img src="/assets/logo.png" alt="جمعية الإرشاد والإصلاح الخيرية الإسلامية" class="img-fluid logo" />
  <form class="form" action="{{url('/login')}}" method="post">
    @csrf
    <input type="text" name="username" class="form-control{{$errors->has('username') ? ' is-invalid' : ''}}"
      placeholder="اسم المستخدم" />
    @if($errors->has("username"))
      <span class="invalid-feedback">{{$errors->first("username")}}</span>
    @endif
    <input type="password" name="password" class="form-control mt-2{{$errors->has('password') ? ' is-invalid' : ''}}"
      placeholder="كلمة المرور" />
    @if($errors->has("password"))
      <span class="invalid-feedback">{{$errors->first("password")}}</span>
    @endif
    <input type="submit" name="submit" class="submit-btn mt-2 btn btn-success text-white btn-block"
      value="تسجيل الدخول" />
  </form>
</div>
@endsection
