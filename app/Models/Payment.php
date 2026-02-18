<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Payment extends Model
{
    protected $table = 'tbl_payment';
    protected $primaryKey = 'payment_id';

    // Disable default timestamps because the table uses custom audit columns
    public $timestamps = false;

    protected $fillable = [
        'invoice_id',   // Foreign key to tbl_invoice
        'payment_date',
        'amount',
        'metode_bayar',
        'bukti_bayar',
        'description',
        'input_by',
        'input_date',
        'edit_by',
        'edit_date',
    ];

    /**
     * Boot logic for automation and metadata.
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

        // Trigger side effects when a payment is recorded
        static::saved(function ($model) {
            if ($model->invoice) {
                // 1. Update the Invoice status to 'Paid' (Status 1)
                $model->invoice->update(['status_invoice' => 1]);

                // 2. Reach through the chain to sync the PO status
                // Path: Invoice -> Delivery -> PO
                $delivery = $model->invoice->delivery;
                if ($delivery && $delivery->po) {
                    $delivery->po->syncStatus();
                }
            }
        });
    }

    // --- RELATIONSHIPS ---

    /**
     * Payment belongs to an Invoice
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }

    /**
     * Access the Purchase Order through the chain: 
     * Payment -> Invoice -> Delivery -> PO
     */
    public function po()
    {
        // Since there is no direct link, we access it through the invoice relationship
        if ($this->invoice && $this->invoice->delivery) {
            return $this->invoice->delivery->po();
        }

        // This returns a dummy/null relationship structure if the chain is broken
        return $this->belongsTo(Po::class, 'invoice_id', 'po_id')->whereRaw('1 = 0');
    }

    /**
     * Helper to get the PO model instance directly
     */
    public function getPoAttribute()
    {
        return $this->invoice?->delivery?->po;
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
