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

  // --- NEW STATUS CONSTANTS (matches your list) ---
  const STATUS_INCOMING = 0;
  const STATUS_OPEN = 1;
  const STATUS_PARTIALLY_DELIVERED = 2; //first check if a PO has a delivery if yes then check how many delivery is linked to that po then get the qty_delivered column from each delivery then sum it up then if the summed value is less than the qty of that PO then the PO status is set to 2 (Partially Delivered)
  const STATUS_PARTIALLY_DELIVERED_PARTIALLY_INVOICED = 4;
  const STATUS_FULLY_DELIVERED_PARTIALLY_INVOICED = 5;
  const STATUS_PARTIALLY_DELIVERED_FULLY_INVOICED = 6;
  const STATUS_CLOSED = 7;   // Fully Delivered & Fully Invoiced

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

  public function invoice()
  {
    return $this->hasOneThrough(
      Invoice::class,
      Delivery::class,
      'po_id',
      'delivery_id',
      'po_id',
      'delivery_id'
    );
  }

  public function investasis()
  {
    return $this->belongsToMany(Investasi::class, 'tbl_investasi_detail', 'po_id', 'id_investasi');
  }

  // --- UPDATED syncStatus METHOD ---
  public function syncStatus($save = true)
  {
    $this->load(['deliveries.invoice.payment']);

    $originalQty = $this->qty;
    $deliveries = $this->deliveries;

    // No deliveries → Open (if already created, otherwise Incoming is set elsewhere)
    if ($deliveries->isEmpty()) {
      $this->status = self::STATUS_OPEN;
      if ($save) $this->save();
      return;
    }

    $totalDelivered = $deliveries->sum('qty_delivered');
    $anyInvoiced = $deliveries->contains(fn($d) => $d->invoiced_status == 1);
    $allInvoiced = $deliveries->every(fn($d) => $d->invoiced_status == 1);

    // Determine delivery completion
    if ($totalDelivered >= $originalQty) {
      $deliveryState = 'full';
    } elseif ($totalDelivered > 0) {
      $deliveryState = 'partial';
    } else {
      $deliveryState = 'none'; // should not happen due to earlier check
    }

    // Determine invoicing state
    if ($allInvoiced) {
      $invoiceState = 'full';
    } elseif ($anyInvoiced) {
      $invoiceState = 'partial';
    } else {
      $invoiceState = 'none';
    }

    // Map to new statuses
    if ($deliveryState == 'full') {
      if ($invoiceState == 'full') {
        $this->status = self::STATUS_CLOSED;                                    // 7
      } elseif ($invoiceState == 'partial') {
        $this->status = self::STATUS_FULLY_DELIVERED_PARTIALLY_INVOICED;       // 5
      } else {
        $this->status = self::STATUS_FULLY_DELIVERED;                           // 3
      }
    } elseif ($deliveryState == 'partial') {
      if ($invoiceState == 'full') {
        $this->status = self::STATUS_PARTIALLY_DELIVERED_FULLY_INVOICED;       // 6
      } elseif ($invoiceState == 'partial') {
        $this->status = self::STATUS_PARTIALLY_DELIVERED_PARTIALLY_INVOICED;   // 4
      } else {
        $this->status = self::STATUS_PARTIALLY_DELIVERED;                       // 2
      }
    } else {
      $this->status = self::STATUS_OPEN;
    }

    if ($save) {
      $this->save();
    }
  }
}
