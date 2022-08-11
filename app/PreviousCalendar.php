<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $calendar_id
 * @property string $date
 * @property string $data
 */
class PreviousCalendar extends Model
{
    /**
     * The primary key for the model.
     * 
     * @var string
     */
    protected $primaryKey = 'calendar_id';


    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = ['date', 'data', 'is_weekly'];

}
