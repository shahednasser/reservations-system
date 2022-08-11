<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $manual_reservation_id
 * @property int $place_requirment_id
 * @property int $nb_days
 * @property PlaceRequirment $placeRequirment
 * @property ManualReservation $manualReservation
 * @property ManualPlaceRequirmentsDate[] $manualPlaceRequirmentsDates
 */
class ManualPlaceRequirment extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['manual_reservation_id', 'place_requirment_id', 'nb_days'];

    /**
     * Indicates if the model should be timestamped.
     * 
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function placeRequirment()
    {
        return $this->belongsTo('App\PlaceRequirment');
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
    public function manualPlaceRequirmentsDates()
    {
        return $this->hasMany('App\ManualPlaceRequirmentsDate');
    }
}
