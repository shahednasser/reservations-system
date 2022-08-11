<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EditRequest extends Model
{
    public $timestamps = false;

    protected $fields = ["reservation_id", "new_reservation_id"];

    public function reservation(){
      return $this->belongsTo("App\Reservation", "reservation_id");
    }

    public function newReservation(){
      return $this->belongsTo("App\Reservation", "new_reservation_id");
    }
}
