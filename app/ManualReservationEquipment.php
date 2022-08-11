<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $manual_reservation_id
 * @property int $equipment_id
 * @property int $number
 * @property int $day_nb
 * @property Equipment $equipment
 * @property ManualReservation $manualReservation
 */
class ManualReservationEquipment extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['manual_reservation_id', 'equipment_id', 'number', 'day_nb'];

    protected $table = 'manual_reservation_equipments';

    /**
     * Indicates if the model should be timestamped.
     * 
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function equipment()
    {
        return $this->belongsTo('App\Equipment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manualReservation()
    {
        return $this->belongsTo('App\ManualReservation');
    }
}
