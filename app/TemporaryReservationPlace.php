<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $temporary_reservation_id
 * @property int $floor_id
 * @property int $room_id
 * @property Floor $floor
 * @property TemporaryReservation $temporaryReservation
 * @property Room $room
 */
class TemporaryReservationPlace extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['temporary_reservation_id', 'floor_id', 'room_id'];

    /**
     * Indicates if the model should be timestamped.
     * 
     * @var bool
     */
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
    public function temporaryReservation()
    {
        return $this->belongsTo('App\TemporaryReservation');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room()
    {
        return $this->belongsTo('App\Room');
    }
}
