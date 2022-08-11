<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $manual_reservations_date_id
 * @property int $manual_place_requirment_id
 * @property ManualPlaceRequirment $manualPlaceRequirment
 * @property ManualReservationsDate $manualReservationsDate
 */
class ManualPlaceRequirmentsDate extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['manual_reservations_date_id', 'manual_place_requirment_id'];

    /**
     * Indicates if the model should be timestamped.
     * 
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manualPlaceRequirment()
    {
        return $this->belongsTo('App\ManualPlaceRequirment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manualReservationsDate()
    {
        return $this->belongsTo('App\ManualReservationsDate');
    }
}
