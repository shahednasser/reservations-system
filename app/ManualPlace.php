<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $floor_id
 * @property int $room_id
 * @property Floor $floor
 * @property Room $room
 * @property ManualReservation[] $manualReservations
 */
class ManualPlace extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['floor_id', 'room_id'];

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
    public function room()
    {
        return $this->belongsTo('App\Room');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function manualReservations()
    {
        return $this->hasMany('App\ManualReservation');
    }
}
