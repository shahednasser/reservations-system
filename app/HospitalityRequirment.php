<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property float $price
 * @property ManualHospitalityRequirment[] $manualHospitalityRequirments
 */
class HospitalityRequirment extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['name', 'price'];

    /**
     * Indicates if the model should be timestamped.
     * 
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function manualHospitalityRequirments()
    {
        return $this->hasMany('App\ManualHospitalityRequirment');
    }
}
