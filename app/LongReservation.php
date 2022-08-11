<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $floor_id
 * @property int $reservation_id
 * @property int $room_id
 * @property int $day_of_the_week
 * @property string $from_time
 * @property string $to_time
 * @property string $event_name
 * @property string $from_date
 * @property string $to_date
 * @property Floor $floor
 * @property Reservation $reservation
 * @property Room $room
 */
class LongReservation extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['reservation_id', 'from_date', 'to_date'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reservation()
    {
        return $this->belongsTo('App\Reservation');
    }

    public function longReservationDates(){
      return $this->hasMany('App\LongReservationDate');
    }
}
