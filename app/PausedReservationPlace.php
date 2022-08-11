<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $paused_reservation_id
 * @property int $floor_id
 * @property int $room_id
 * @property Floor $floor
 * @property PausedReservation $pausedReservation
 * @property Room $room
 */
class PausedReservationPlace extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['paused_reservation_id', 'floor_id', 'room_id'];
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
    public function pausedReservation()
    {
        return $this->belongsTo('App\PausedReservation');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room()
    {
        return $this->belongsTo('App\Room');
    }
}
