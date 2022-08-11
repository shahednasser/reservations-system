<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $manual_reservation_id
 * @property int $hospitality_requirment_id
 * @property int $nb_days
 * @property HospitalityRequirment $hospitalityRequirment
 * @property ManualReservation $manualReservation
 */
class ManualHospitalityRequirment extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['manual_reservation_id', 'hospitality_requirment_id', 'nb_days', 'additional_name', 'additional_price'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hospitalityRequirment()
    {
        return $this->belongsTo('App\HospitalityRequirment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manualReservation()
    {
        return $this->belongsTo('App\ManualReservation');
    }
}
