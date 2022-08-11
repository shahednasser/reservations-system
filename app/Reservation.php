<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $committee
 * @property string $event_name
 * @property string $notes
 * @property string $supervisors
 * @property string $date_created
 * @property int $is_approved
 * @property User $user
 * @property LongReservation[] $longReservations
 * @property TemporaryReservation[] $temporaryReservations
 */
class Reservation extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['user_id', 'committee', 'event_name', 'notes', 'supervisors', 'date_created', 'is_approved',
        'message'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function longReservation()
    {
        return $this->hasOne('App\LongReservation');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function temporaryReservation()
    {
        return $this->hasOne('App\TemporaryReservation');
    }

    public function editedReservation(){
      return $this->hasOne("App\EditedReservation");
    }

    public function deleteRequest(){
      return $this->hasOne("App\DeleteRequest");
    }

    public function hasEditRequest(){
      return $this->hasOne("App\EditRequest", "reservation_id");
    }

    public function isEditRequest(){
      return $this->hasOne("App\EditRequest", "new_reservation_id");
    }

    public function reservationsRejection(){
      return $this->hasOne("App\ReservationsRejection");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function pausedReservation()
    {
        return $this->hasOne('App\PausedReservation');
    }

    public function getStatus(){
      switch($this->is_approved){
        case 0:
          if($this->editedReservation){
            return __("معدل");
          }
          return __("الطلب مرسل");
        case 1:
          return __("موافق عليه");
        case -1:
          return __("مرفوض");
      }
    }

    public function getFullStatus(){
      switch($this->is_approved){
        case 0:
          if($this->editedReservation){
            return ["status-edited", __("معدل"), 0];
          }
          return ["status-normal", __("الطلب مرسل"), 0];
        case 1:
          return ["status-success", __("موافق عليه"), 1];
        case -1:
          return ["status-fail", __("مرفوض"), -1];
        case -2:
            return ["status-revoked", __('ملغي'), -2];
      }
    }
}
