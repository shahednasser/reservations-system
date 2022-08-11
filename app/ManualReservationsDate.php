<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $manual_reservation_id
 * @property string $date
 * @property string $from_time
 * @property string $to_time
 * @property int $for_women
 * @property int $for_men
 * @property ManualReservation $manualReservation
 * @property ManualPlaceRequirmentsDate[] $manualPlaceRequirmentsDates
 */
class ManualReservationsDate extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['manual_reservation_id', 'date', 'from_time', 'to_time', 'for_women', 'for_men'];

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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function manualPlaceRequirmentsDates()
    {
        return $this->hasMany('App\ManualPlaceRequirmentsDate');
    }
}
