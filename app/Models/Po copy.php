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

    protected $fillable = [
        'customer_id',
        'no_po',
        'nama_barang',
        'tgl_po',
        'periode_po',
        'tgl_invoice', // Shadow field for form processing
        'invoice_number', // Shadow field for form processing
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
            // 1. Calculate Total Selling Price
            $model->total = $model->qty * $model->harga;

            // 2. FIXED: Modal is always 50% of the Total
            // If Total = 20,000,000 -> Modal = 10,000,000
            $model->modal_awal = $model->total * 0.5;

            // 3. Margin is passed from Controller (3,000,000)
            // We just ensure margin_unit is calculated correctly
            if ($model->qty > 0) {
                $model->margin_unit = $model->margin / $model->qty;
            } else {
                $model->margin_unit = 0;
            }

            // 4. Metadata
            $model->periode_po = \Carbon\Carbon::parse($model->tgl_po)->format('Y-m');
            $model->status = $model->status ?? 0;
            $model->input_by = \Illuminate\Support\Facades\Auth::id() ?? 1;
            $model->input_date = now();
        });

        static::saved(function ($model) {
            // Automation: Delivery & Invoice creation (Only if Status allows)
            if ($model->status === 'Delivered' || !empty($model->invoice_number)) {
                $delivery = \App\Models\Delivery::firstOrCreate(
                    ['po_id' => $model->po_id],
                    [
                        'delivery_no' => 'DO-' . $model->no_po . '-' . time(),
                        'qty_delivered' => $model->qty,
                        'delivered_at' => now(),
                        'delivered_status' => 2,
                        'input_by' => Auth::id() ?? 1,
                    ]
                );

                if (!empty($model->invoice_number)) {
                    \App\Models\Invoice::firstOrCreate(
                        ['delivery_id' => $delivery->delivery_id],
                        [
                            'nomor_invoice' => $model->invoice_number,
                            'tgl_invoice'   => $model->tgl_invoice ?? now(),
                            'due_date'      => $model->tgl_invoice ? Carbon::parse($model->tgl_invoice)->addDays(60) : now()->addDays(60),
                            'input_by'      => Auth::id() ?? 1,
                        ]
                    );
                }
            }
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
        $deliveries = $this->deliveries;

        if ($deliveries->isEmpty()) {
            $this->status = 'Open';
            if ($save) $this->save();
            return;
        }

        $totalQtyOrdered = $this->qty;
        $totalQtyDelivered = $deliveries->sum('qty_delivered');

        // Status 2 = Delivered, Status 1 = Invoiced based on your SQL comments
        $allDelivered = $deliveries->every(fn($d) => $d->delivered_status == 2);
        $allInvoiced = $deliveries->every(fn($d) => $d->invoiced_status == 1);

        // Check if every delivery has an invoice, and every invoice has a payment
        $allPaid = $deliveries->every(
            fn($d) =>
            $d->invoice !== null && $d->invoice->payment !== null
        );

        if ($totalQtyDelivered >= $totalQtyOrdered && $allDelivered && $allInvoiced && $allPaid) {
            $this->status = 'Closed';
        } elseif ($deliveries->contains(fn($d) => $d->invoice !== null && $d->invoice->payment !== null)) {
            $this->status = 'Delivered & Partially Paid';
        } elseif ($allInvoiced) {
            $this->status = 'Delivered & Invoiced';
        } else {
            $this->status = 'Delivered';
        }

        if ($save) {
            $this->save();
        }
    }
}
