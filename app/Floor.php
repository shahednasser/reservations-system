<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property int $number_of_rooms
 * @property LongReservation[] $longReservations
 * @property Room[] $rooms
 * @property TemporaryReservationPlace[] $temporaryReservationPlaces
 */
class Floor extends Model
{

  use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    /**
     * @var array
     */
    /**
     * @var array
     */
    protected $fillable = ['name', 'number_of_rooms'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function longReservations()
    {
        return $this->hasMany('App\LongReservation');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rooms()
    {
        return $this->hasMany('App\Room');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function temporaryReservationPlaces()
    {
        return $this->hasMany('App\TemporaryReservationPlace');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pausedReservationPlaces()
    {
        return $this->hasMany('App\PausedReservationPlace');
    }
}
