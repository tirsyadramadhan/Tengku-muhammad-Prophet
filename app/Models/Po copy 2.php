<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Po extends Model
{
    protected $table = 'tbl_po';
    protected $primaryKey = 'po_id';
    public $timestamps = false;

    const STATUS_INCOMING = 0;
    const STATUS_OPEN = 1;
    const STATUS_PARTIALLY_DELIVERED = 2;
    const STATUS_PARTIALLY_INVOICED = 3;
    const STATUS_FULLY_DELIVERED = 4;
    const STATUS_FULLY_INVOICED = 5;
    const STATUS_PARTIALLY_DELIVERED_AND_PARTIALLY_INVOICED = 6;
    const STATUS_CLOSED = 7;
    protected $fillable = [
        'customer_id',
        'no_po',
        'nama_barang',
        'tgl_po',
        'periode_po',
        'tgl_invoice',
        'invoice_number',
        'due_date',
        'qty',
        'harga',
        'total',
        'modal_awal',
        'margin',
        'margin_unit',
        'status',
        'input_by',
        'input_date',
        'edit_date',
        'edit_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->total = $model->qty * $model->harga;
            $model->modal_awal = $model->total * 0.5; // Fixed 50% rule

            if ($model->qty > 0) {
                $model->margin_unit = $model->margin / $model->qty;
            } else {
                $model->margin_unit = 0;
            }

            $model->periode_po = \Carbon\Carbon::parse($model->tgl_po)->format('Y-m');
            $model->status = $model->status ?? self::STATUS_INCOMING;
            $model->input_by = \Illuminate\Support\Facades\Auth::id() ?? 1;
            $model->input_date = now();
        });
    }

    // --- RELATIONSHIPS ---

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id_cust');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'po_id', 'po_id');
    }

    /**
     * Get the invoice through the delivery table
     */
    public function invoice()
    {
        // Since PO -> Delivery -> Invoice
        return $this->hasOneThrough(
            Invoice::class,
            Delivery::class,
            'po_id',       // Foreign key on tbl_delivery
            'delivery_id', // Foreign key on tbl_invoice
            'po_id',       // Local key on tbl_po
            'delivery_id'  // Local key on tbl_delivery
        );
    }

    public function investasis()
    {
        return $this->belongsToMany(Investasi::class, 'tbl_investasi_detail', 'po_id', 'id_investasi');
    }

    // --- LOGIC ---

    public function syncStatus($save = true)
    {
        $this->load(['deliveries.invoice.payment']);

        $originalQty = $this->qty;                       // original ordered quantity
        $deliveries = $this->deliveries;

        if ($deliveries->isEmpty()) {
            $this->status = self::STATUS_OPEN;            // 1
            if ($save) $this->save();
            return;
        }

        $totalDelivered = $deliveries->sum('qty_delivered');
        $anyInvoiced = $deliveries->contains(fn($d) => $d->invoiced_status == 1);
        $allInvoiced = $deliveries->every(fn($d) => $d->invoiced_status == 1);
        $allPaid = $deliveries->every(fn($d) => $d->invoice && $d->invoice->payment);

        // Determine status based on quantities and invoicing
        if ($totalDelivered >= $originalQty) {
            // Fully delivered (quantity-wise)
            if ($allInvoiced && $allPaid) {
                $this->status = self::STATUS_CLOSED;                      // 7
            } elseif ($allInvoiced) {
                $this->status = self::STATUS_FULLY_INVOICED;              // 5
            } else {
                $this->status = self::STATUS_FULLY_DELIVERED;             // 4
            }
        } elseif ($totalDelivered > 0) {
            // Partially delivered
            if ($anyInvoiced) {
                $this->status = self::STATUS_PARTIALLY_DELIVERED_AND_PARTIALLY_INVOICED; // 6
            } elseif ($allInvoiced) {
                // All existing deliveries are invoiced, but total < original → partially invoiced
                $this->status = self::STATUS_PARTIALLY_INVOICED;          // 3
            } else {
                $this->status = self::STATUS_PARTIALLY_DELIVERED;         // 2
            }
        } else {
            // No deliveries (should not happen due to earlier check)
            $this->status = self::STATUS_OPEN;
        }

        if ($save) {
            $this->save();
        }
    }
}
