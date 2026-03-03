<?php

namespace App\Models;

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
    const STATUS_FULLY_DELIVERED = 3;
    const STATUS_PARTIALLY_DELIVERED_PARTIALLY_INVOICED = 4;
    const STATUS_FULLY_DELIVERED_PARTIALLY_INVOICED = 5;
    const STATUS_PARTIALLY_DELIVERED_FULLY_INVOICED = 6;
    const STATUS_FULLY_DELIVERED_FULLY_INVOICED = 7;
    const STATUS_CLOSED = 8;

    protected $fillable = [
        'customer_id',
        'no_po',
        'nama_barang',
        'tgl_po',
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

            $model->status = $model->status ?? self::STATUS_INCOMING;
            $model->input_by = Auth::id() ?? 1;
            $model->input_date = now();
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id_cust');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'po_id', 'po_id');
    }

    public function invoices()
    {
        return $this->hasManyThrough(
            Invoice::class,
            Delivery::class,
            'po_id',
            'delivery_id',
            'po_id',
            'delivery_id'
        );
    }

    public static function syncAll()
    {
        self::where('status', '!=', self::STATUS_INCOMING)
            ->with(['deliveries', 'invoices'])
            ->chunkById(200, function ($pos) {
                foreach ($pos as $po) {
                    $po->syncStatus(true);
                }
            });
    }

    public function syncStatus($save = true)
    {
        $this->load('deliveries');
        $deliveries = $this->deliveries;

        if ($deliveries->isEmpty()) {
            if ($this->status != self::STATUS_INCOMING) {
                $this->status = self::STATUS_OPEN;
            }
            if ($save && $this->exists) {
                $this->save();
            }
            return;
        }

        $sumDelivered = $deliveries->sum('qty_delivered');
        $poQty = $this->qty;

        $totalDeliveriesCount = $deliveries->count();
        $invoicedCount = $deliveries->where('invoiced_status', 1)->count();

        $isPartiallyDelivered = ($sumDelivered < $poQty);
        $isFullyDelivered     = ($sumDelivered >= $poQty);

        $isUninvoiced        = ($invoicedCount == 0);
        $isAllInvoiced       = ($invoicedCount == $totalDeliveriesCount && $totalDeliveriesCount > 0);
        $isPartiallyInvoiced = ($invoicedCount > 0 && $invoicedCount < $totalDeliveriesCount);

        if ($isPartiallyDelivered) {

            if ($isUninvoiced) {
                $this->status = self::STATUS_PARTIALLY_DELIVERED;
            } elseif ($isPartiallyInvoiced) {
                $this->status = self::STATUS_PARTIALLY_DELIVERED_PARTIALLY_INVOICED;
            } elseif ($isAllInvoiced) {
                $this->status = self::STATUS_PARTIALLY_DELIVERED_FULLY_INVOICED;
            }
        } elseif ($isFullyDelivered) {
            if ($isUninvoiced) {
                $this->status = self::STATUS_FULLY_DELIVERED;
            } elseif ($isPartiallyInvoiced) {
                $this->status = self::STATUS_FULLY_DELIVERED_PARTIALLY_INVOICED;
            } elseif ($isAllInvoiced) {
                $unpaidInvoices = $this->relationLoaded('invoices')
                    ? $this->invoices->where('status_invoice', '!=', 1)->count()
                    : $this->invoices()->where('status_invoice', '!=', 1)->count();

                if ($unpaidInvoices > 0) {
                    $this->status = self::STATUS_FULLY_DELIVERED_FULLY_INVOICED;
                } else {
                    $this->status = self::STATUS_CLOSED;
                }
            }
        }

        if ($save && $this->exists) {
            $this->save();
        }
    }
    public function input_user()
    {
        return $this->belongsTo(User::class, 'input_by', 'user_id');
    }
}
