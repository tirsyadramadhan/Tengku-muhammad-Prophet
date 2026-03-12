<?php

namespace App\Imports;

use App\Models\Po;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class PoImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{

    public function collection(Collection $rows)
    {
        // Define status mapping from string to integer
        $statusMap = [
            'Incoming'  => 0,
            'Open'      => 1,
            'Delivered' => 7,
            'Close'     => 8
        ];

        // Group rows by no_po to sum quantities and margins
        $grouped = $rows->groupBy('no_po');

        foreach ($grouped as $noPo => $poRows) {
            $firstRow = $poRows->first();

            // Sum qty and margin across all rows with the same no_po
            $totalQty = $poRows->sum('qty');
            $totalMargin = $poRows->sum('margin');

            $dueDate = !empty($firstRow['payment_date'])
                ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($firstRow['payment_date'])
                : null;

            // Determine the integer status value from the first row's status string
            $statusValue = $statusMap[$firstRow['status']] ?? null; // default to null if not mapped

            // 1. Create or update Purchase Order with summed qty, margin, and correct status
            $po = Po::firstOrCreate(
                ['no_po' => $noPo],
                [
                    'customer_id'     => 1,
                    'nama_barang'     => $firstRow['nama_barang'],
                    'tgl_po'          => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($firstRow['tgl_po']),
                    'qty'             => $totalQty,
                    'harga'           => $firstRow['harga'],
                    'total'           => $firstRow['total'],
                    'modal_awal'      => $firstRow['modal_awal'],
                    'margin'          => $totalMargin,           // summed margin
                    'margin_unit'     => $firstRow['margin_unit'],
                    'tambahan_margin' => $firstRow['tambahan_margin'],
                    'status'          => $statusValue,           // FIX: set status based on row status
                    'input_by'        => Auth::id() ?? 1,
                ]
            );

            // If PO already existed, update qty and margin (and optionally status)
            if (!$po->wasRecentlyCreated) {
                $po->update([
                    'qty'    => $totalQty,
                    'margin' => $totalMargin,
                    // Optionally update status if it might have changed:
                    // 'status' => $statusValue,
                ]);
            }

            // 2. Process deliveries/invoices per row (only for Delivered/Close)
            foreach ($poRows as $row) {
                $nomorInvoice = 'INV-' . $noPo;
                $deliveryNo   = 'DEL-' . $noPo;
                $status       = $row['status'];

                $isDelivered = ($status === 'Delivered');
                $isClosed    = ($status === 'Close');

                // Only create deliveries/invoices for Delivered or Close
                if (!$isDelivered && !$isClosed) {
                    continue;
                }

                // Create Delivery for PO
                $delivery = null;
                if (!empty($row['delivered'])) {
                    $delivery = Delivery::create([
                        'delivery_no'              => $deliveryNo,
                        'po_id'                     => $po->po_id,
                        'qty_delivered'             => $row['delivered'],
                        'delivery_time_estimation'  => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['delivered_at']),
                        'delivered_at'               => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['delivered_at']),
                        'delivered_status'           => $isClosed ? 1 : 0,
                        'invoiced_status'            => $row['delivered'] ? 1 : 0,
                        'input_by'                   => Auth::id() ?? 1,
                    ]);
                }

                // Create Invoice for Delivery
                $invoice = null;
                if ($delivery && !empty($row['invoiced_at'])) {
                    $invoice = Invoice::create([
                        'nomor_invoice'  => $nomorInvoice,
                        'delivery_id'    => $delivery->delivery_id,
                        'tgl_invoice'    => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['invoiced_at']),
                        'due_date'       => $dueDate,
                        'status_invoice' => 0,
                        'input_by'       => Auth::id(),
                    ]);
                }

                // Create Payment for Invoice
                $payment = null;
                if (!empty($row['payment_date']) && $invoice && ($isClosed || $isDelivered)) {

                    $estimationDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['payment_date']);

                    // Close = paid, Delivered = still waiting
                    $paymentStatus = $isClosed ? 1 : 0;

                    $payment = Payment::create([
                        'invoice_id'              => $invoice->invoice_id,
                        'payment_date'            => $estimationDate,
                        'amount'                  => $row['total'],
                        'description'             => $row['catatan'],
                        'metode_bayar'            => null,
                        'bukti_bayar'             => null,
                        'payment_status'          => $paymentStatus,
                        'payment_date_estimation' => $estimationDate,
                        'input_by'                => Auth::id(),
                    ]);

                    if ($payment->payment_status === 1) {
                        $invoice->update(['status_invoice' => 1]);
                    }
                }
            }
        }
    }
}
