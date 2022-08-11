<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $long_reservation_date_id
 * @property int $room_id
 * @property int $floor_id
 * @property LongReservationDate $longReservationDate
 * @property Room $room
 */
class LongReservationPlace extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['long_reservation_date_id', 'room_id', 'floor_id'];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function floor()
    {
        return $this->belongsTo('App\Floor');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function longReservationDate()
    {
        return $this->belongsTo('App\LongReservationDate');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room()
    {
        return $this->belongsTo('App\Room');
    }
}
