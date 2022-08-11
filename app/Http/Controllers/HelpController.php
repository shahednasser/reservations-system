<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class HelpController extends Controller
{

    protected $sections = [];

    public function __construct()
    {
        $this->middleware('auth');
        $common = ["home-page" => __('الصفحة الرئيسية'),
                    "calendar" => __('الرزنامة'),
                    "add-reservation" => __('إضافة حجز'),
                    "edit-reservation" => __('تعديل حجز'),
                    "delete-reservation" => __('حذف حجز'),
                    "reservation-page" => __('صفحة حجز'),
                    "reservation-status" => __('حالة الحجز'),
                    "my-account" => __('حسابي'),
                    "logout" => __('تسجيل الخروج')];
        $this->sections = ["admin" => array_merge($common,
                                            ["reservation-requests" =>
                                                __('طلبات الحجوزات'),
                                            "all-reservations" => __('جميع الحجوزات'),
                                            "reservation-page" => __('صفحة حجز'),
                                            "request-page" => __('صفحة طلب الحجز'),
                                            "all-accounts" => __('جميع الحسابات'),
                                            "add-user" => __('إضافة مستخدم'),
                                            "edit-user" => __('تعديل مستخدم'),
                                            "delete-user" => __('حذف مستخدم'),
                                            "manage-places" => __('إدارة الأماكن')]) ,
                    "user" => $common];
    }

    public function showHelp($section = null){
        $user = Auth::user();
        if(!$section){
            $section = 'home-page';
        }
        if($user->isAdmin()){
            $type = "admin";
        }
        else{
            $type = "user";
        }
        if(!in_array($section, array_keys($this->sections[$type]))){
            abort(404);
        }
        return view('help', ["user" => $user, "section" => $section, "sections" => $this->sections[$type]]);
    }

    public function showUpdates(){
      $user = Auth::user();
      return view('updates', ["user" => $user]);
    }
}
