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

    /**
     * CHANGED: From belongsTo to belongsToMany
     * This allows accessing $investasi->pos
     */
    public function pos()
    {
        // define the pivot table name and keys
        return $this->belongsToMany(Po::class, 'tbl_investasi_detail', 'id_investasi', 'po_id');
    }
}
