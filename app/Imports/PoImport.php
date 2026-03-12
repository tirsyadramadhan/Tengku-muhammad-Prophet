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
        $statusMap = [
            'Incoming'  => 0,
            'Open'      => 1,
            'Delivered' => 7,
            'Close'     => 8
        ];

        foreach ($rows as $index => $row) {
            $noPo       = $row['no_po'];
            $namaBarang = $row['nama_barang'];
            $statusValue = $statusMap[$row['status']] ?? null;

            $po = Po::create([
                'customer_id'     => 1,
                'no_po'           => $noPo,
                'nama_barang'     => $namaBarang,
                'tgl_po'          => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tgl_po']),
                'qty'             => $row['qty'],
                'harga'           => $row['harga'],
                'total'           => $row['total'],
                'modal_awal'      => $row['modal_awal'],
                'margin'          => $row['margin'],
                'margin_unit'     => $row['margin_unit'],
                'tambahan_margin' => $row['tambahan_margin'],
                'status'          => $statusValue,
                'input_by'        => Auth::id() ?? 1,
            ]);

            $status      = $row['status'];
            $isClosed    = ($status === 'Close');

            $deliveryNo   = 'DEL-' . $noPo . '-' . ($index + 1);
            $nomorInvoice = 'INV-' . $noPo . '-' . ($index + 1);

            $dueDate = !empty($row['payment_date'])
                ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['payment_date'])
                : null;

            $delivery = null;
            if (!empty($row['delivered'])) {
                $delivery = Delivery::create([
                    'delivery_no'              => $deliveryNo,
                    'po_id'                    => $po->po_id,
                    'qty_delivered'            => $row['delivered'],
                    'delivery_time_estimation' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['delivered_at']),
                    'delivered_at'             => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['delivered_at']),
                    'delivered_status'         => $isClosed ? 1 : 0,
                    'invoiced_status'          => !empty($row['invoiced_at']) ? 1 : 0,
                    'input_by'                 => Auth::id() ?? 1,
                ]);
            }

            $invoice = null;
            if ($delivery && !empty($row['invoiced_at'])) {
                $invoice = Invoice::create([
                    'nomor_invoice'  => $nomorInvoice,
                    'delivery_id'    => $delivery->delivery_id,
                    'tgl_invoice'    => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['invoiced_at']),
                    'due_date'       => $dueDate,
                    'status_invoice' => $isClosed ? 1 : 0,
                    'input_by'       => Auth::id() ?? 1,
                ]);
            }

            if ($invoice && !empty($row['payment_date'])) {
                $estimationDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['payment_date']);

                Payment::create([
                    'invoice_id'              => $invoice->invoice_id,
                    'payment_date'            => $isClosed ? $estimationDate : null,
                    'amount'                  => $row['total'],
                    'description'             => $row['catatan'],
                    'metode_bayar'            => null,
                    'bukti_bayar'             => null,
                    'payment_status'          => $isClosed ? 1 : 0,
                    'payment_date_estimation' => $estimationDate,
                    'input_by'                => Auth::id() ?? 1,
                ]);
            }
        }
    }
}
