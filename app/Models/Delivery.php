<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Delivery extends Model
{
    protected $table = 'tbl_delivery';
    protected $primaryKey = 'delivery_id';

    // Disable Laravel default timestamps because the table uses custom names (input_date, edit_date)
    public $timestamps = false;

    protected $fillable = [
        'po_id',
        'delivery_no',              // Added: Required by UNIQUE constraint in schema
        'qty_delivered',
        'delivery_time_estimation',
        'delivered_at',
        'delivered_status',         // 0: Pending, 1: Shipped, 2: Delivered
        'invoiced_status',          // 0: Uninvoiced, 1: Invoiced
        'input_by',
        'input_date',
        'edit_by',
        'edit_date',
    ];

    /**
     * Boot the model to handle automatic fields and PO status syncing.
     */
    protected static function boot()
    {
        parent::boot();

        // Before creating: Set metadata
        static::creating(function ($model) {
            $model->input_by = $model->input_by ?? Auth::id() ?? 1;
            $model->input_date = now();
        });

        // Before updating: Set edit metadata
        static::updating(function ($model) {
            $model->edit_by = Auth::id() ?? 1;
            $model->edit_date = now();
        });

        // After saving: Tell the PO to recalculate its status based on this delivery
        static::saved(function ($model) {
            if ($model->po) {
                $model->po->syncStatus();
            }
        });
    }

    // --- RELATIONSHIPS ---

    /**
     * Each delivery belongs to one PO (tbl_po)
     */
    public function po()
    {
        return $this->belongsTo(Po::class, 'po_id', 'po_id');
    }

    /**
     * Each delivery can have one invoice (tbl_invoice)
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'delivery_id', 'delivery_id');
    }

    /**
     * Relationship to the user who created this delivery record
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'input_by', 'user_id');
    }

    /**
     * Relationship to the user who last edited this delivery record
     */
    public function editor()
    {
        return $this->belongsTo(User::class, 'edit_by', 'user_id');
    }
}
