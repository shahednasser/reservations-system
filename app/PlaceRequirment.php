<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property float $price
 * @property ManualPlaceRequirment[] $manualPlaceRequirments
 * @property ManualPlaceRequirmentsDate[] $manualPlaceRequirmentsDates
 */
class PlaceRequirment extends Model
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
    public function manualPlaceRequirments()
    {
        return $this->hasMany('App\ManualPlaceRequirment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function manualPlaceRequirmentsDates()
    {
        return $this->hasMany('App\ManualPlaceRequirmentsDate');
    }
}
