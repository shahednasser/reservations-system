<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $temporary_reservation_id
 * @property string $date
 * @property string $from_time
 * @property string $to_time
 * @property TemporaryReservation $temporaryReservation
 */
class TemporaryReservationDate extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['temporary_reservation_id', 'date', 'from_time', 'to_time'];

    /**
     * Indicates if the model should be timestamped.
     * 
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function temporaryReservation()
    {
        return $this->belongsTo('App\TemporaryReservation');
    }
}
