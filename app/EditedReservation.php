<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EditedReservation extends Model
{
    protected $fields = ["reservation_id"];
    public $timestamps = false;

    public function reservation(){
      return $this->belongsTo("App\Reservation");
    }
}
