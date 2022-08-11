<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property ManualReservationEquipment[] $manualReservationEquipments
 */
class Equipment extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'equipments';

    /**
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * Indicates if the model should be timestamped.
     * 
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function manualReservationEquipments()
    {
        return $this->hasMany('App\ManualReservationEquipment');
    }
}
