<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $reservation_id
 * @property int $manual_reservation_id
 * @property string $from_date
 * @property string $to_date
 * @property Reservation $reservation
 * @property ManualReservation $manualReservation
 */
class PausedReservation extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['reservation_id', 'manual_reservation_id', 'from_date', 'to_date'];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reservation()
    {
        return $this->belongsTo('App\Reservation');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manualReservation()
    {
        return $this->belongsTo('App\ManualReservation');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pausedReservationPlaces()
    {
        return $this->hasMany('App\PausedReservationPlace');
    }
}
