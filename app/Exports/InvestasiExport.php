<?php

namespace App\Exports;

use App\Models\Investasi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvestasiExport implements FromCollection, WithHeadings, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Investasi::select('modal_setor_awal', 'modal_po_baru', 'margin', 'pencairan_modal', 'margin_cair', 'pengembalian_dana', 'dana_tersedia', 'tgl_investasi')
            ->get()
            ->map(fn($investasi) => [
                'modal_setor_awal'           => $investasi->modal_setor_awal,
                'modal_po_baru'     => $investasi->modal_po_baru,
                'margin'          => $investasi->margin,
                'pencairan_modal'             => $investasi->pencairan_modal,
                'margin_cair'           => $investasi->margin_cair,
                'pengembalian_dana'           => $investasi->pengembalian_dana,
                'dana_tersedia'      => $investasi->dana_tersedia,
                'tgl_investasi'          => $investasi->tgl_investasi
            ]);
    }

    public function headings(): array
    {
        // column headers in the Excel file
        return ['Modal Setor Awal', 'Modal PO Baru', 'Margin', 'Pencairan Modal', 'Margin Cair', 'Pengembalian Dana', 'Dana Tersedia', 'Tanggal Investasi'];
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
