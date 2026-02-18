<?php

namespace App\Models;

use App\Models\Investasi;

use Illuminate\Database\Eloquent\Model;

class MarginDiterima extends Model
{
    protected $table = 'tbl_margin_diterima';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'margin_diterima',
    ];
}
