<?php

namespace App\Console\Commands;

use App\PreviousCalendar;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\CalendarFunctions;

class SaveCalendar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'save:calendar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save latest calendar data of the day';

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
        $today = Carbon::now();
        $currentDate = date_format($today, "d-m-Y");
        $resources = CalendarFunctions::getLocations();
        //get events
        //$events = $this->getEvents($currentDate);
        $events = CalendarFunctions::getEvents($currentDate);

        $previousCalendar = new PreviousCalendar([
            'date' => date_format($today, 'Y-m-d'),
            'data' => json_encode(["resources" => $resources, "events" => $events])
        ]);
        $previousCalendar->save();

        //get for weekly calendar
        $events = CalendarFunctions::getEvents($currentDate, true);
        $previousCalendar = new PreviousCalendar([
            'date' => date_format($today, 'Y-m-d'),
            'data' => json_encode($events),
            'is_weekly' => 1
        ]);
        $previousCalendar->save();
    }
}
