<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $manual_place_id
 * @property string $full_name
 * @property string $organization
 * @property string $mobile_phone
 * @property string $home_phone
 * @property string $event_name
 * @property string $event_type
 * @property string $date_created
 * @property ManualPlace $manualPlace
 * @property ManualHospitalityRequirment[] $manualHospitalityRequirments
 * @property ManualPlaceRequirment[] $manualPlaceRequirments
 * @property ManualReligiousRequirment[] $manualReligiousRequirments
 * @property ManualReservationEquipment[] $manualReservationEquipments
 * @property ManualReservationsDate[] $manualReservationsDates
 */
class ManualReservation extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['manual_place_id', 'full_name', 'organization', 'mobile_phone', 'home_phone', 'event_name', 'event_type',
                            'date_created', 'discount', 'is_approved'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manualPlace()
    {
        return $this->belongsTo('App\ManualPlace');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function manualHospitalityRequirments()
    {
        return $this->hasMany('App\ManualHospitalityRequirment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function manualPlaceRequirments()
    {
        return $this->hasMany('App\ManualPlaceRequirment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function manualReligiousRequirments()
    {
        return $this->hasMany('App\ManualReligiousRequirment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function manualReservationEquipments()
    {
        return $this->hasMany('App\ManualReservationEquipment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function manualReservationsDates()
    {
        return $this->hasMany('App\ManualReservationsDate');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function pausedReservation()
    {
        return $this->hasOne('App\PausedReservation');
    }
}
