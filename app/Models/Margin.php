<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Margin extends Model
{
    protected $table = 'tbl_margin';
    protected $primaryKey = 'id_margin';
    public $timestamps = false;

    protected $fillable = [
        'investasi_dikembalikan',
        'margin_tersedia',
        'margin_diterima'
    ];

    public function po()
    {
        return $this->belongsTo(Po::class, 'po_id', 'po_id');
    }
}
