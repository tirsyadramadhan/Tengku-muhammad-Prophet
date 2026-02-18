<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Po extends Model
{
    protected $table = 'tbl_po';
    protected $primaryKey = 'po_id';
    public $timestamps = false;

    // --- STATUS CONSTANTS (Updated 0-8) ---
    const STATUS_INCOMING = 0;
    const STATUS_OPEN = 1;
    const STATUS_PARTIALLY_DELIVERED = 2;
    const STATUS_FULLY_DELIVERED = 3;
    const STATUS_PARTIALLY_DELIVERED_PARTIALLY_INVOICED = 4;
    const STATUS_FULLY_DELIVERED_PARTIALLY_INVOICED = 5;
    const STATUS_PARTIALLY_DELIVERED_FULLY_INVOICED = 6;
    const STATUS_FULLY_DELIVERED_FULLY_INVOICED = 7; // Goods In, Billed, but NOT Paid
    const STATUS_CLOSED = 8; // Goods In, Billed, AND Paid

    protected $fillable = [
        'customer_id',
        'no_po',
        'nama_barang',
        'tgl_po',
        'periode_po',
        'qty',
        'harga',
        'total',
        'modal_awal',
        'margin',
        'margin_unit',
        'tambahan_margin',
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
            $model->modal_awal = $model->total * 0.5;

            if ($model->qty > 0) {
                $model->margin_unit = $model->margin / $model->qty;
            } else {
                $model->margin_unit = 0;
            }

            $model->periode_po = \Carbon\Carbon::parse($model->tgl_po)->format('Y-m');
            $model->status = $model->status ?? self::STATUS_INCOMING;
            $model->input_by = Auth::id() ?? 1;
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

    // Used to check payment status for Status 8
    public function invoices()
    {
        return $this->hasManyThrough(
            Invoice::class,
            Delivery::class,
            'po_id',       // Foreign key on tbl_delivery table
            'delivery_id', // Foreign key on tbl_invoice table
            'po_id',       // Local key on tbl_po table
            'delivery_id'  // Local key on tbl_delivery table
        );
    }

    // --- BULK SYNC HELPER ---
    public static function syncAll()
    {
        // 1. FILTER: Ignore 'Closed' (8) statuses to save processing time.
        // 2. EAGER LOAD: Load 'deliveries' in one shot. This fixes the lag.
        $pos = self::where('status', '!=', self::STATUS_CLOSED)
            ->with('deliveries')
            ->get();

        foreach ($pos as $po) {
            // We pass 'false' for loadFromDb because we already loaded deliveries above
            $po->syncStatus(true);
        }
    }

    // --- MAIN SYNC LOGIC ---
    public function syncStatus($save = true)
    {
        // 1. LOAD DATA
        $this->load('deliveries');
        $deliveries = $this->deliveries;

        // 2. CHECK IF ANY DELIVERIES EXIST
        // If no deliveries, it's either Incoming (0) or Open (1).
        if ($deliveries->isEmpty()) {
            // If it's not Incoming, ensure it's Open.
            if ($this->status != self::STATUS_INCOMING) {
                $this->status = self::STATUS_OPEN;
            }
            // Only save if it exists in DB to prevent creating null records
            if ($save && $this->exists) {
                $this->save();
            }
            return;
        }

        // 3. CALCULATE METRICS
        $sumDelivered = $deliveries->sum('qty_delivered');
        $poQty = $this->qty;

        $totalDeliveriesCount = $deliveries->count();
        $invoicedCount = $deliveries->where('invoiced_status', 1)->count();

        // 4. DETERMINE LOGIC FLAGS

        // Delivery Status
        $isPartiallyDelivered = ($sumDelivered < $poQty);
        $isFullyDelivered     = ($sumDelivered >= $poQty);

        // Invoicing Status (Based on Delivery 'invoiced_status')
        $isUninvoiced        = ($invoicedCount == 0);
        $isAllInvoiced       = ($invoicedCount == $totalDeliveriesCount && $totalDeliveriesCount > 0);
        $isPartiallyInvoiced = ($invoicedCount > 0 && $invoicedCount < $totalDeliveriesCount);

        // 5. APPLY STATUS HIERARCHY

        if ($isPartiallyDelivered) {
            // --- PARTIAL DELIVERY BRANCH ---

            if ($isUninvoiced) {
                // Status 2: Partially Delivered (No Invoices)
                $this->status = self::STATUS_PARTIALLY_DELIVERED;
            } elseif ($isPartiallyInvoiced) {
                // Status 4: Partially Delivered & Partially Invoiced
                $this->status = self::STATUS_PARTIALLY_DELIVERED_PARTIALLY_INVOICED;
            } elseif ($isAllInvoiced) {
                // Status 6: Partially Delivered & Fully Invoiced
                $this->status = self::STATUS_PARTIALLY_DELIVERED_FULLY_INVOICED;
            }
        } elseif ($isFullyDelivered) {
            // --- FULL DELIVERY BRANCH ---

            if ($isUninvoiced) {
                // Status 3: Fully Delivered (No Invoices)
                $this->status = self::STATUS_FULLY_DELIVERED;
            } elseif ($isPartiallyInvoiced) {
                // Status 5: Fully Delivered & Partially Invoiced
                $this->status = self::STATUS_FULLY_DELIVERED_PARTIALLY_INVOICED;
            } elseif ($isAllInvoiced) {
                // --- FULLY DELIVERED & FULLY INVOICED BRANCH ---
                // Now we must decide between Status 7 and Status 8 based on Payment.

                // Check actual invoice records for payment status (status_invoice = 1)
                $unpaidInvoices = $this->invoices()->where('status_invoice', '!=', 1)->count();

                if ($unpaidInvoices > 0) {
                    // Status 7: Fully Delivered & Fully Invoiced (But NOT all paid)
                    $this->status = self::STATUS_FULLY_DELIVERED_FULLY_INVOICED;
                } else {
                    // Status 8: Closed (Fully Delivered, Fully Invoiced, & Fully Paid)
                    $this->status = self::STATUS_CLOSED;
                }
            }
        }

        // 6. SAVE
        if ($save && $this->exists) {
            $this->save();
        }
    }
}
