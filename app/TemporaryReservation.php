<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $reservation_id
 * @property string $equipment_needed
 * @property Reservation $reservation
 * @property TemporaryReservationDate[] $temporaryReservationDates
 * @property TemporaryReservationPlace[] $temporaryReservationPlaces
 */
class TemporaryReservation extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['reservation_id', 'equipment_needed_1', 'equipment_needed_2', 'equipment_needed_3'];

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function temporaryReservationDates()
    {
        return $this->hasMany('App\TemporaryReservationDate');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function temporaryReservationPlaces()
    {
        return $this->hasMany('App\TemporaryReservationPlace');
    }
}
