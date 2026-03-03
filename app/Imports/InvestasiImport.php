<?php

namespace App\Imports;

use App\Models\Investasi;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;


class InvestasiImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    private function parseDate($value)
    {
        if (empty($value)) return null;

        // Excel serial number (numeric)
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                ->format('Y-m-d');
        }

        // String like "7/25/2026"
        try {
            return \Carbon\Carbon::createFromFormat('m/d/Y', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null; // bad date? store null, don't explode
        }
    }
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            Investasi::create([
                'modal_setor_awal'  => $this->toFloat($row['modal_setor_awal']),
                'modal_po_baru'     => $this->toFloat($row['modal_po_baru']),
                'margin'            => $this->toFloat($row['margin']),
                'pencairan_modal'   => $this->toFloat($row['pencairan_modal']),
                'margin_cair'       => $this->toFloat($row['margin_cair']),
                'pengembalian_dana' => $this->toFloat($row['pengembalian_dana']),
                'dana_tersedia'     => $this->toFloat($row['dana_tersedia']),
                'tgl_investasi'     => $this->parseDate($row['tanggal']),
            ]);
        }
    }
    private function toFloat($value): ?float
    {
        if (is_null($value) || $value === '') return null;
        return (float) $value;
    }
}
