<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Invoice extends Model
{
    protected $table = 'tbl_invoice';
    protected $primaryKey = 'invoice_id';

    // Disable Laravel default timestamps as the table uses custom names
    public $timestamps = false;

    protected $fillable = [
        'delivery_id',      // Foreign key to tbl_delivery
        'nomor_invoice',    // Unique invoice number
        'tgl_invoice',
        'due_date',
        'status_invoice',   // 0: Unpaid, 1: Paid, 2: Cancelled
        'input_by',
        'input_date',
        'edit_by',
        'edit_date',
    ];

    /**
     * Boot the model to handle metadata and status synchronization.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->input_by = $model->input_by ?? Auth::id() ?? 1;
            $model->input_date = now();
        });

        static::updating(function ($model) {
            $model->edit_by = Auth::id() ?? 1;
            $model->edit_date = now();
        });

        // Trigger updates in parent records after an invoice is saved
        static::saved(function ($model) {
            // Force-load the delivery relationship (in case it wasn't loaded)
            $model->load('delivery.po');

            if ($model->delivery) {
                // Update delivery invoiced_status
                $model->delivery->update(['invoiced_status' => 1]);

                // Trigger PO status sync
                if ($model->delivery->po) {
                    $model->delivery->po->syncStatus();
                }
            }
        });
    }

    // --- RELATIONSHIPS ---

    /**
     * Each invoice belongs to exactly one delivery
     */
    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id', 'delivery_id');
    }

    /**
     * Access the PO through the Delivery relationship
     */
    public function po()
    {
        return $this->hasOneThrough(
            Po::class,
            Delivery::class,
            'delivery_id', // Foreign key on tbl_delivery
            'po_id',       // Foreign key on tbl_po
            'delivery_id', // Local key on tbl_invoice
            'po_id'        // Local key on tbl_delivery
        );
    }

    /**
     * Each invoice can have one payment record
     */
    public function payment()
    {
        return $this->hasOne(Payment::class, 'invoice_id', 'invoice_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'input_by', 'user_id');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'edit_by', 'user_id');
    }
}
