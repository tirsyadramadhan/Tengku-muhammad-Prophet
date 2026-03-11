<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investasi extends Model
{
    protected $table = 'tbl_investasi';
    protected $primaryKey = 'id_investasi';
    public $timestamps = false;

    protected $fillable = [
        'modal_setor_awal',
        'modal_po_baru',
        'margin',
        'pencairan_modal',
        'margin_cair',
        'pengembalian_dana',
        'dana_tersedia',
        'tgl_investasi'
    ];
}
