<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;
use Validator;
use Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected $pagination_nb = 20;
    public function __construct(){
      $this->middleware('auth');
      $this->middleware('admin')->only(["deleteUser", "getUsers", 'showEditUser', 'addUser', 'editUser', 'showAddUser']);
    }

    public function logout(){
      Auth::logout();
      return redirect('/login');
    }

    public function viewAccount($id = null){
      $user = Auth::user();
      if($id && !$user->isAdmin() && $user->id != $id){
        abort(404);
      }

      $user_account = null;
      if($id){
        if($id == $user->id){
          $user_account = $user;
        }
        else{
          $user_account = User::find($id);
          if(!$user_account){
            abort(404);
          }
        }
      }
      else{
        $user_account = $user;
      }

      return view('view-account', ['user' => $user, 'user_account' => $user_account]);
    }

    public function deleteUser(Request $request, $id){
      $user = User::find($id);
      if(!$user) {
        abort(404);
      }

      $user->delete();
      $request->session()->flash('message', __('تم حذف المستخدم بنجاح.'));
      $request->session()->flash('message_class', 'success');
      return redirect("/");
    }

    public function getUsers(){
      $users = User::orderBy('name', 'asc')->simplePaginate($this->pagination_nb);
      $user = Auth::user();

      return view("view-users", ["user" => $user, "users" => $users]);
    }

    public function showEditUser($id){
      $user_account = User::find($id);
      if(!$user_account){
        abort(404);
      }

      $user = Auth::user();

      return view('user-form', ["is_editing" => true, "user_account" => $user_account, "user" => $user]);
    }

    public function addUser(Request $request){
      Validator::make($request->all(), [
        "name" => 'required',
        'username' => 'required|unique:users,username|min:8|alpha_dash',
        'password' => 'required|confirmed|min:8',
      ])->validate();

      $user = new User([
        "name" => $request->name,
        "username" => $request->username,
        "is_admin" => $request->is_admin ? 1 : 0,
        "position" => $request->position,
        "is_maintainer" => $request->is_maintainer ? 1 : 0
      ]);

      $hashed = Hash::make($request->password);
      $user->password = $hashed;
      $user->save();

      $request->session()->flash("message", __("تم إضافة المستخدم بنجاح"));
      $request->session()->flash("message_class", "success");

      return redirect("/view-account/".$user->id);
    }

    public function editUser(Request $request, $id){
      $user = User::find($id);
      if(!$user){
        abort(404);
      }

      Validator::make($request->all(), [
        "name" => 'required',
        'username' => [
          'required',
          Rule::unique('users')->ignore($user->id),
          'alpha_dash',
          'min:8'
        ],
        'password' => 'nullable|confirmed|min:8',
      ])->validate();

      $user->name = $request->name;
      $user->username = $request->username;
      $user->position = $request->position;
      $user->is_admin = $request->is_admin ? 1 : 0;
      $user->is_maintainer = $request->is_maintainer ? 1 : 0;

      if($request->password !== null){
        $hashed = Hash::make($request->password);
        $user->password = $hashed;
      }
      $user->save();

      $request->session()->flash("message", __("تم تعديل معلومات المستخدم بنجاح"));
      $request->session()->flash("message_class", "success");

      return redirect("/view-account/$id");
    }

    public function showAddUser(){
      $user = Auth::user();

      return view('user-form', ["is_editing" => false, "user_account" => null, "user" => $user]);
    }
}
