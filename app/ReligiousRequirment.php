<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property float $price
 * @property ManualReligiousRequirment[] $manualReligiousRequirments
 */
class ReligiousRequirment extends Model
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
    public function manualReligiousRequirments()
    {
        return $this->hasMany('App\ManualReligiousRequirment');
    }
}
