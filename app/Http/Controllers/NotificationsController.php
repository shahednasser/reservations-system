<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Notifications\UserNotifications;

class NotificationsController extends Controller
{
    public function __construct(){
      $this->middleware('auth');
    }

    public function getNotifications(){
      $user = Auth::user();

      return response()->json($user->notifications);
    }
    /**
     * If timestamp is specified, get notifications after timestamp
     * If no timestamp is specified, get unread notifications
     *
     */
    public function getNewNotifications($timestamp = null)
    {
      $user = Auth::user();

      if($timestamp){
        $notifications = [];
        $user->notifications->each(function($value) use(&$notifications, $timestamp){
          if(strtotime($value->created_at) >= $timestamp){
            $notifications[] = $value;
          }
          else{
            return false;
          }
        });
      }
      else{
        $notifications = $user->unreadNotifications;
      }

      return response()->json($notifications);
    }

    public function readNotifications(Request $request){
      $user = Auth::user();
      foreach ($user->unreadNotifications as $notification) {
        $notification->markAsRead();
      }

      return response()->json(["success" => "success"]);
    }

    public function test(){
      Auth::user()->notify(new UserNotifications('test', '/test', Auth::user()->id));
      return response()->json(["success"=>"success"]);
    }
}
