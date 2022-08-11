<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{

  use Notifiable;
  use SoftDeletes;
    /**
     * @var array
     */
    protected $fillable = ['username', 'password', 'position', 'is_admin', 'remember_token', 'name',
                            'is_maintainer', 'deleted_at'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservations()
    {
        return $this->hasMany('App\Reservation');
    }

    public function isAdmin(){
      return $this->is_admin == 1;
    }
}
