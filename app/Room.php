<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $floor_id
 * @property int $room_number
 * @property string $name
 * @property Floor $floor
 * @property LongReservation[] $longReservations
 * @property TemporaryReservationPlace[] $temporaryReservationPlaces
 */
class Room extends Model
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
    protected $fillable = ['floor_id', 'room_number', 'name'];

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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function longReservations()
    {
        return $this->hasMany('App\LongReservation');
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
