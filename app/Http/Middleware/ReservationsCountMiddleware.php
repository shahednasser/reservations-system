<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\Reservation;
use App\DeleteRequest;

class ReservationsCountMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if($user && $user->isAdmin()){
          $reservations = Reservation::where("is_approved", 0)->count();
          $delete_reservations = DeleteRequest::count();
          view()->share('requests_count', $reservations + $delete_reservations);
        }

        return $next($request);
    }
}
