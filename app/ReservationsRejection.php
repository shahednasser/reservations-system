<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReservationsRejection extends Model
{
    protected $fillable = ["reservation_id", "message"];

    public $timestamps = false;

    public function Reservation(){
      return $this->belongsTo("App\Reservation");
    }
}
