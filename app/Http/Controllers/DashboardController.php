<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Reservation;
use App\TemporaryReservation;
use App\LongReservation;
use Carbon\Carbon;
use App\Floor;
use App\ManualReservation;
use Auth;
use App\User;

class DashboardController extends Controller
{
    protected $pagination_nb = 15;

    public function __construct(){
      $this->middleware("auth");
    }

    public function showDashboard(){
        $user = Auth::user();

        //get new reservations
        $new_reservations = null;
        if($user->isAdmin()){
          $new_reservations = Reservation::where("is_approved", 0)->limit(5)->get();
          $new_reservations->load(["user", "temporaryReservation", "longReservation"]);
        }

        return view("home")->withUser($user)
                              ->with("new_reservations", $new_reservations);
    }

    public function search(Request $request){
      $search = $request->input('search');
      if(!$search){
        return redirect('/');
      }

      $reservationResults = Reservation::where("event_name", "like", "%$search%")->orWhere("committee", "like", "%$search%")
                            ->get();
      $manReservationResults = ManualReservation::where("event_name", "like", "%$search%")
                                                  ->orWhere("organization", "like", "%$search%")
                                                  ->orWhere("full_name", "like", "%$search%")
                                                  ->orWhere("event_type", "like", "%$search%")
                                                  ->get();
      $users = User::where("name", "like", "%$search%")->orWhere("username", "like", "%$search%")->get();
      $results = ($reservationResults->concat($manReservationResults->concat($users)))->shuffle();
      $allResults = paginate($results, $this->pagination_nb)->setPath($request->path());

      $user = Auth::user();

      return view('search-results', ["user" => $user, "results" => $allResults, "search" => $search]);
    }
}
