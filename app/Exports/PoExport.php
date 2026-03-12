<?php

namespace App\Exports;

use App\Models\Po;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PoExport implements FromCollection, WithHeadings, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    const STATUS = [
        0 => 'Incoming',
        1 => 'Open',
        2 => 'Partially Delivered',
        3 => 'Fully Delivered',
        4 => 'Partially Delivered & Partially Invoiced',
        5 => 'Fully Delivered & Partially Invoiced',
        6 => 'Partially Delivered & Fully Invoiced',
        7 => 'Fully Delivered & Fully Invoiced',
        8 => 'Closed',
    ];
    public function collection()
    {
        return Po::select('no_po', 'nama_barang', 'tgl_po', 'qty', 'harga', 'total', 'modal_awal', 'margin', 'margin_unit', 'tambahan_margin', 'status')
            ->get()
            ->map(fn($po) => [
                'no_po'           => $po->no_po,
                'nama_barang'     => $po->nama_barang,
                'tgl_po'          => $po->tgl_po,
                'qty'             => $po->qty,
                'harga'           => $po->harga,
                'total'           => $po->total,
                'modal_awal'      => $po->modal_awal,
                'margin'          => $po->margin,
                'margin_unit'     => $po->margin_unit,
                'tambahan_margin' => $po->tambahan_margin,
                'status'          => self::STATUS[$po->status] ?? 'Unknown',
            ]);
    }

    public function headings(): array
    {
        // column headers in the Excel file
        return ['No PO', 'Nama Barang', 'Tanggal PO', 'Jumlah Barang', 'Harga Per Unit', 'Total PO', 'Modal', 'Total Margin', 'Margin Per Unit', 'Tambahan Margin', 'Status PO'];
    }

    public function styles(Worksheet $sheet)
    {
        // FIX: apply full borders to all cells that have data
        $lastRow    = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();
        $range      = 'A1:' . $lastColumn . $lastRow;

        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // bold the heading row
        $sheet->getStyle('1')->getFont()->setBold(true);
    }
}
