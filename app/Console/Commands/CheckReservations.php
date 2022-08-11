<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\Notifications\UserNotifications;
use App\LongReservation;
use App\TemporaryReservationDate;
use App\ManualReservationsDate;

class CheckReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check reservation timings';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      $users = User::where('is_maintainer', 1)->get();
      if(!$users->count()){
        return;
      }
      $dt = \Carbon\Carbon::now();
      $dateString = $dt->toDateString();
      $day_of_week = $dt->dayOfWeek;
      $hour = $dt->hour;
      $minute = $dt->minute;
      $second = $dt->second;
      $now = $hour.":".$minute.':00';
      $soondt = new \Carbon\Carbon($dt->copy());
      $soondt->addMinutes(10);
      $soon = $soondt->hour.":".$soondt->minute.":00";
      $longReservations = LongReservation::where('from_date', '<=', $dt->toDateString())
                                              ->where('to_date', '>=', $dt->toDateString())
                                              ->get();
      $nowLongReservations = [];
      $soonLongReservations = [];
      $longReservations->each(function($value) use(&$nowLongReservations, &$soonLongReservations, $dt, $day_of_week,
                                                    $now, $soon, $dateString){
        if($value->reservation->is_approved == 1){
          $lrds = $value->longReservationDates()->get();
          foreach($lrds as $lrd) {
            if ($lrd->day_of_week == $day_of_week && !isInsidePausePeriod($value->reservation, $value->from_date, $value->to_date, $dateString, $dateString, true, $lrd->day_of_week)) {
              if($lrd->from_time == $now) {
                $lrd->load('longReservation');
                $nowLongReservations[] = $lrd;
              }
              elseif ($now < $lrd->from_time && $soon >= $lrd->from_time) {
                $soonLongReservations[] = $lrd;
              }
            }
          }
        }
      });
      $nowLongReservations = collect($nowLongReservations);
      $soonLongReservations = collect($soonLongReservations);
      $nowTempReservations = TemporaryReservationDate::where('date', $dateString)
                                                      ->where('from_time', $now)->get()
                                                      ->reject(function($value) use($dateString){
                                                        return isPausedOnDay($value->temporaryReservation->reservation, $dateString);
                                                      });
      $nowTempReservations->load('temporaryReservation.reservation');
      $nowManualReservations = ManualReservationsDate::where('date', $dt->toDateString())
                                                      ->where('from_time', $now)->get()
                                                      ->reject(function($value) use($dateString){
                                                        return isPausedOnDay($value->manualReservation, $dateString);
                                                      });
      $nowManualReservations->load('manualReservation');
      $soonTempReservations = TemporaryReservationDate::where('date', $dt->toDateString())
                                                      ->where('from_time', '<=', $soon)
                                                      ->where('from_time', '>', $now)->get()
                                                      ->reject(function($value) use($dateString){
                                                        return isPausedOnDay($value->temporaryReservation->reservation, $dateString);
                                                      });
      $soonTempReservations->load('temporaryReservation.reservation');
      $soonManualReservations = ManualReservationsDate::where('date', $dt->toDateString())
                                                      ->where('from_time', '<=', $soon)
                                                      ->where('from_time', '>', $now)->get()
                                                      ->reject(function($value) use($dateString){
                                                        return isPausedOnDay($value->manualReservation, $dateString);
                                                      });
      $soonManualReservations->load('manualReservation');
      foreach($users as $user){
        $nowLongReservations->each(function($item) use ($user){
          $reservation = $item->longReservation->reservation;
          echo "notifying about now\n";
          $user->notify(new UserNotifications(__('حجز ').$reservation->event_name.__(' قد بدأ'),
                                              url('/show-reservation/'.$reservation->id)));
        });
        $soonLongReservations->each(function($item) use ($user, $dt){
          $reservation = $item->longReservation->reservation;
          $resdt = \Carbon\Carbon::createFromTimeString($item->from_time, config("app.timezone"));
          $resdt->second(0);
          $dt->second(0);
          $diff = intval($resdt->diffInMinutes($dt));
          $diff = $diff == 1 ? __('دقيقة') : ($diff == 2 ? __('دقيقتين') : $diff.__(' دقائق'));
          echo "notifying about soon res".$reservation->id;
          $user->notify(new UserNotifications(__('حجز ').$reservation->event_name.__(' سوف يبدأ بعد ').$diff,
                                              url('/show-reservation/'.$reservation->id)));
        });
        $nowTempReservations->each(function($item) use ($user){
          $reservation = $item->temporaryReservation->reservation;
          if($reservation->is_approved == 1){
              echo "notifying about now\n";
            $user->notify(new UserNotifications(__('حجز ').$reservation->event_name.__(' قد بدأ'),
                                                url('/show-reservation/'.$reservation->id)));
          }
        });
        $soonTempReservations->each(function($item) use ($user, $dt){
          $reservation = $item->temporaryReservation->reservation;
          if($reservation->is_approved == 1){
            $resdt = \Carbon\Carbon::createFromTimeString($item->from_time, config("app.timezone"));
            $resdt->second(0);
            $dt->second(0);
            $diff = intval($resdt->diffInMinutes($dt));
            $diff = $diff == 1 ? __('دقيقة') : ($diff == 2 ? __('دقيقتين') : $diff.__(' دقائق'));
            echo "notifying about soon res ".$reservation->id;
            $user->notify(new UserNotifications(__('حجز ').$reservation->event_name.__(' سوف يبدأ بعد ').$diff,
                                                url('/show-reservation/'.$reservation->id)));
          }
        });
        $nowManualReservations->each(function($item) use ($user){
          $reservation = $item->manualReservation;
          echo "notifying about now\n";
          $user->notify(new UserNotifications(__('حجز ').$reservation->event_type.__(' قد بدأ'),
                                              url('/view-admin-reservation/'.$reservation->id)));
        });
        $soonManualReservations->each(function($item) use ($user, $dt){
          $reservation = $item->manualReservation;
          $resdt = \Carbon\Carbon::createFromTimeString($item->from_time, config("app.timezone"));
          $resdt->second(0);
          $dt->second(0);
          $diff = intval($resdt->diffInMinutes($dt));
          $diff = $diff == 1 ? __('دقيقة') : ($diff == 2 ? __('دقيقتين') : $diff.__(' دقائق'));
          echo "notifying about soon ".$reservation->id;
          $user->notify(new UserNotifications(__('حجز ').$reservation->event_type.__(' سوف يبدأ بعد ').$diff,
                                              url('/view-admin-reservation/'.$reservation->id)));
        });
    }
  }
}
