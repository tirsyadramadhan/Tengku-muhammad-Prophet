<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Summary extends Model
{
    protected $table = 'summary';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'investasi_id',
        'dana_tersedia',
        'investasi_dikembalikan',
        'investasi_tambahan',
        'investasi_ditahan',
        'total_investasi_transfer',
        'total_transfer_investasi',
        'margin_diterima',
        'margin_tersedia',
        'margin_ditahan',
        'total_margin',
        'sisa_margin',
    ];

    public function investasi()
    {
        return $this->belongsTo(Investasi::class, 'investasi_id', 'id_investasi');
    }
}
