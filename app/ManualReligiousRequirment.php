<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $manual_reservation_id
 * @property int $religious_requirment_id
 * @property int $nb_days
 * @property ManualReservation $manualReservation
 * @property ReligiousRequirment $religiousRequirment
 */
class ManualReligiousRequirment extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['manual_reservation_id', 'religious_requirment_id', 'nb_days'];

    /**
     * Indicates if the model should be timestamped.
     * 
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manualReservation()
    {
        return $this->belongsTo('App\ManualReservation');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function religiousRequirment()
    {
        return $this->belongsTo('App\ReligiousRequirment');
    }
}
