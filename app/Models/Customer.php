<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'tbl_customer';
    protected $primaryKey = 'id_cust';
    
    // Disable standard Laravel timestamps because your SQL uses input_date/edit_date
    public $timestamps = false;

    protected $fillable = [
        'cust_name', 
        'input_by', 
        'input_date', 
        'edit_date', 
        'edit_by'
    ];
}