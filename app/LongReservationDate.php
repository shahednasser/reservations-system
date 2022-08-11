<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LongReservationDate extends Model
{
      /**
       * @var array
       */
      protected $fillable = ['long_reservation_id', 'day_of_week', 'from_time', 'to_time', 'event'];

      /**
       * Indicates if the model should be timestamped.
       *
       * @var bool
       */
      public $timestamps = false;

      /**
       * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
       */
      public function longReservation()
      {
          return $this->belongsTo('App\LongReservation');
      }

    public function longReservationPlaces(){
        return $this->hasMany('App\LongReservationPlace');
    }
}
