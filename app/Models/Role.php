<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'tbl_role';
    protected $primaryKey = 'role_id';
    public $timestamps = false;

    protected $fillable = ['role_name', 'input_by', 'input_date', 'edit_date', 'edit_by'];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }
}
