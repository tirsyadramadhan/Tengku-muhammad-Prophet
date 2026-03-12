<?php

namespace App\Imports;

use App\Models\Po;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class PoImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    private int $customerId;

    public function __construct(int $customerId = 1)
    {
        $this->customerId = $customerId;
    }

    // ─── STATUS MAP ───────────────────────────────────────────────────────────────
    // Excel string → DB tinyint
    const STATUS_MAP = [
        'incoming'  => 0,
        'open'      => 1,
        'delivered' => 7, // Fully Delivered & Fully Invoiced
        'close'     => 8,
        'closed'    => 8,
    ];

    // ─── DATE CONVERSION ─────────────────────────────────────────────────────────
    private function toDate($value): ?string
    {
        if (!$value) return null;
        if (!is_numeric($value)) return Carbon::parse($value)->format('Y-m-d');
        return Carbon::instance(
            \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int) $value)
        )->format('Y-m-d');
    }

    // ─── MAIN COLLECTION HANDLER ─────────────────────────────────────────────────
    public function collection(Collection $rows)
    {
        // STEP 1 — group rows by PO
        // Rows with no_po = null are continuation rows (extra deliveries) for the previous PO
        $groups = [];
        $currentKey = null;

        foreach ($rows as $row) {
            $noPo = $row['no_po'];

            if ($noPo) {
                // new PO — start a new group
                $currentKey = (string) $noPo;
                $groups[$currentKey] = [
                    'main' => $row,      // first row holds PO-level data
                    'deliveries' => [$row] // first row is also delivery #1
                ];
            } elseif ($currentKey) {
                // null no_po = extra delivery row for the current PO
                $groups[$currentKey]['deliveries'][] = $row;
            }
        }

        // STEP 2 — wrap everything in a transaction
        // if any row fails, nothing gets saved — keeps the DB consistent
        DB::transaction(function () use ($groups) {
            foreach ($groups as $noPo => $group) {
                $main       = $group['main'];
                $deliveries = $group['deliveries'];

                $tglPo     = $this->toDate($main['tgl_po']);
                $statusKey = strtolower(trim($main['status'] ?? 'open'));
                $status    = self::STATUS_MAP[$statusKey] ?? 1;
                $namaBarang = trim(str_replace("\xA0", ' ', $main['nama_barang'] ?? ''));

                // ── CREATE / UPDATE PO ────────────────────────────────────────────
                $po = Po::updateOrCreate(
                    ['no_po' => $noPo],
                    [
                        'customer_id'     => $this->customerId,
                        'nama_barang'     => $namaBarang,
                        'tgl_po'          => $tglPo,
                        'periode_po'      => $tglPo ? Carbon::parse($tglPo)->format('Y-m') : null,
                        'qty'             => $main['qty']             ?? 0,
                        'harga'           => $main['harga']           ?? 0,
                        'total'           => $main['total']           ?? 0,
                        'modal_awal'      => $main['modal_awal']      ?? 0,
                        'margin'          => $main['margin']          ?? 0,
                        'margin_unit'     => $main['margin_unit']     ?? 0,
                        'tambahan_margin' => $main['tambahan_margin'] ?? null,
                        'status'          => $status,
                        'input_by'        => Auth::id(),
                    ]
                );

                // ── SKIP deliveries / invoices / payments for Open & Incoming ─────
                // These POs haven't been delivered yet — no child records needed
                if (in_array($status, [0, 1])) continue;

                // ── CREATE DELIVERIES ─────────────────────────────────────────────
                // Split total payment evenly across all delivery rows
                $totalAmount    = (float) ($main['total'] ?? 0);
                $deliveryCount  = count($deliveries);
                $amountPerDelivery = $deliveryCount > 0 ? $totalAmount / $deliveryCount : $totalAmount;

                foreach ($deliveries as $index => $deliveryRow) {
                    $deliveryNumber = $index + 1;

                    // Auto-generate delivery_no since it's not in the Excel
                    // Format: DEL-{no_po}-{sequence}
                    $deliveryNo = 'DEL-' . $noPo . '-' . $deliveryNumber;

                    $deliveredAt = $this->toDate($deliveryRow['delivered_at']);

                    // delivered_status: 2 = Delivered (since these rows have delivered > 0)
                    // invoiced_status:  1 = Invoiced if invoiced_at is present, 0 otherwise
                    $deliveredStatus = 2;
                    $invoicedStatus  = $deliveryRow['invoiced_at'] ? 1 : 0;

                    $delivery = Delivery::updateOrCreate(
                        ['delivery_no' => $deliveryNo],
                        [
                            'po_id'                    => $po->po_id,
                            'qty_delivered'            => $deliveryRow['delivered'] ?? 0,
                            'delivered_at'             => $deliveredAt,
                            'delivered_status'         => $deliveredStatus,
                            'invoiced_status'          => $invoicedStatus,
                            'input_by'                 => Auth::id(),
                        ]
                    );

                    // ── CREATE INVOICE ────────────────────────────────────────────
                    // Only create invoice if invoiced_at exists
                    if (!$deliveryRow['invoiced_at']) continue;

                    $tglInvoice = $this->toDate($deliveryRow['invoiced_at']);

                    // Prefer 30-day due date, fall back to 60-day
                    $dueDate = $deliveryRow['invoice_due_30_days']
                        ? $this->toDate($deliveryRow['invoice_due_30_days'])
                        : $this->toDate($deliveryRow['invoice_due_60_days']);

                    // Auto-generate nomor_invoice since it's not in the Excel
                    // Format: INV-{no_po}-{sequence}
                    $nomorInvoice = 'INV-' . $noPo . '-' . $deliveryNumber;

                    // status_invoice: 1 = Paid if payment_date exists, 0 = Unpaid otherwise
                    $statusInvoice = $deliveryRow['payment_date'] ? 1 : 0;

                    $invoice = Invoice::updateOrCreate(
                        ['nomor_invoice' => $nomorInvoice],
                        [
                            'delivery_id'    => $delivery->delivery_id,
                            'tgl_invoice'    => $tglInvoice,
                            'due_date'       => $dueDate,
                            'status_invoice' => $statusInvoice,
                            'input_by'       => Auth::id(),
                        ]
                    );

                    // ── CREATE PAYMENT ────────────────────────────────────────────
                    // Only create payment if payment_date exists
                    if (!$deliveryRow['payment_date']) continue;

                    $paymentDate = $this->toDate($deliveryRow['payment_date']);

                    Payment::updateOrCreate(
                        ['invoice_id' => $invoice->invoice_id],
                        [
                            'payment_date' => $paymentDate,
                            'amount'       => $amountPerDelivery,
                            'description'  => $deliveryRow['catatan'] ?? null,
                            'input_by'     => Auth::id(),
                        ]
                    );
                }
            }
        });
    }
}
