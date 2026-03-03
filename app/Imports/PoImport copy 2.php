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
            // Add other statuses if needed, e.g.:
            // 'Delivered' => 2,
            // 'Close'     => 3,
        ];

        // Group rows by no_po to sum quantities and margins
        $grouped = $rows->groupBy('no_po');

        foreach ($grouped as $noPo => $poRows) {
            $firstRow = $poRows->first();

            // Sum qty and margin across all rows with the same no_po
            $totalQty = $poRows->sum('qty');
            $totalMargin = $poRows->sum('margin');

            // Parse dates only if they exist
            $rawDueDate = !empty($firstRow['invoice_due_60_days'])
                ? $firstRow['invoice_due_60_days']
                : $firstRow['invoice_due_30_days'];

            $dueDate = !empty($rawDueDate)
                ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDueDate)
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

                // Parse delivered_at if present
                $dateValue = !empty($row['delivered_at'])
                    ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['delivered_at'])
                    : null;

                // Create Delivery
                $delivery = null;
                if (!empty($row['delivered'])) {
                    $delivery = Delivery::create([
                        'delivery_no'              => $deliveryNo,
                        'po_id'                     => $po->po_id,
                        'qty_delivered'             => $row['delivered'],
                        'delivery_time_estimation'  => $dateValue,
                        'delivered_at'               => $dateValue,
                        'delivered_status'           => 1,
                        'invoiced_status'            => 1,
                        'input_by'                   => Auth::id() ?? 1,
                    ]);
                }

                // Create Invoice and possibly Payment
                if (!empty($row['invoiced_at']) && $delivery) {
                    $invoicedAt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['invoiced_at']);

                    $invoice = Invoice::create([
                        'nomor_invoice'  => $nomorInvoice,
                        'delivery_id'    => $delivery->delivery_id,
                        'tgl_invoice'    => $invoicedAt,
                        'due_date'       => $dueDate,
                        'status_invoice' => 1,
                        'input_by'       => Auth::id(),
                    ]);

                    // Payment only for "Close" status
                    if ($isClosed) {
                        Payment::create([
                            'invoice_id'   => $invoice->invoice_id,
                            'payment_date' => $dueDate,
                            'amount'       => $row['total'],
                            'description'  => $row['catatan'],
                            'metode_bayar' => "Tunai",
                            'bukti_bayar'  => "Tidak Ada",
                            'input_by'     => Auth::id(),
                        ]);
                    }
                }
            }
        }
    }
}
