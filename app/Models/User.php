<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'tbl_user';
    protected $primaryKey = 'user_id';

    // Disable Laravel's default created_at/updated_at if using your custom columns
    public $timestamps = false;

    protected $fillable = [
        'user_name',
        'password',
        'role_id',
        'last_login',
        'input_by',
        'input_date',
        'edit_date',
        'edit_by',
    ];

    protected $hidden = [
        'password',
    ];

    // Tell Laravel which column to use for the password during login
    public function getAuthPassword()
    {
        return $this->password;
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    /**
     * Check if the user has a specific role
     */
    public function hasRole($roleName)
    {
        return $this->role && $this->role->role_name === $roleName;
    }
}
