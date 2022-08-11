<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\PausedReservation;

class CheckPausedReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:pausedReservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if paused reservations to date has changed';

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
        //get current date
        $dt = \Carbon\Carbon::now();
        //get paused reservations
        $pausedReservations = PausedReservation::all();
        foreach($pausedReservations as $pausedReservation){
            $to_date = createDate($pausedReservation->to_date);
            if($dt->greaterThan($to_date)){
                $pausedReservation->delete();
            }
        }
    }
}