<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Hash;
use Auth;
use App\User;

class GuestController extends Controller
{
  public function __construct(){
    $this->middleware("guest");
  }

  public function showLogin(){
    return view("login");
  }

  public function login(Request $request){
    $validator = Validator::make($request->all(), [
      "username" => [
        "required",
        "exists:users,username"
      ],
      "password" => "required"
    ])->validate();

    $user = User::where("username", $request->username)->first();
    if(!$user){
      return back()->withErrors(["username" => "المستخدم غير موجود"]);
    }

    if(!Hash::check($request->password, $user->password)){
      return back()->withErrors(["password" => "كلمة المرور غير صحيحة"]);
    }

    Auth::login($user, true);
    return redirect("/");
  }
}
