<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Role;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The roles that belong to the user.
    */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Check if the user has admin or staff role.
     */
    public function isAdminOrStaff()
    {
        foreach ($this->roles()->get() as $role)
        {
            if ($role->name == 'admin' || $role->name == 'staff')
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the user has admin role.
    */
    public function isAdmin()
    {
        foreach ($this->roles()->get() as $role)
        {
            if ($role->name == 'admin')
            {
                return true;
            }
        }

        return false;
    }
}
