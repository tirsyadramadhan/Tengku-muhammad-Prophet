<?php

namespace App\DataTables;

use App\Models\Summary;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SummaryDataTable extends DataTable
{
    public function dataTable(mixed $query): EloquentDataTable
    {
        return datatables()
            ->collection($query)
            ->addColumn('tgl_investasi', function (Summary $row) {
                return $row->investasi?->tgl_investasi
                    ? Carbon::parse($row->investasi->tgl_investasi)->translatedFormat('d M Y')
                    : '-';
            })
            ->orderColumn('tgl_investasi', function (Builder $query, string $order) {
                $query->orderBy('tbl_investasi.tgl_investasi', $order);
            })
            ->filterColumn('tgl_investasi', function (Builder $query, string $keyword) {
                if ($keyword) {
                    $query->whereDate('tbl_investasi.tgl_investasi', $keyword);
                }
            })
            ->addColumn('dana_tersedia', function (Summary $row) {
                return self::rupiah($row->dana_tersedia);
            })
            ->orderColumn('dana_tersedia', function (Builder $query, string $order) {
                $query->orderBy('summary.dana_tersedia', $order);
            })
            ->filterColumn('dana_tersedia', function (Builder $query, string $keyword) {
                self::filterDecimal($query, 'summary.dana_tersedia', $keyword);
            })
            ->addColumn('investasi_dikembalikan', function (Summary $row) {
                return self::rupiah($row->investasi_dikembalikan);
            })
            ->orderColumn('investasi_dikembalikan', function (Builder $query, string $order) {
                $query->orderBy('summary.investasi_dikembalikan', $order);
            })
            ->filterColumn('investasi_dikembalikan', function (Builder $query, string $keyword) {
                self::filterDecimal($query, 'summary.investasi_dikembalikan', $keyword);
            })
            ->addColumn('investasi_tambahan', function (Summary $row) {
                return self::rupiah($row->investasi_tambahan);
            })
            ->orderColumn('investasi_tambahan', function (Builder $query, string $order) {
                $query->orderBy('summary.investasi_tambahan', $order);
            })
            ->filterColumn('investasi_tambahan', function (Builder $query, string $keyword) {
                self::filterDecimal($query, 'summary.investasi_tambahan', $keyword);
            })
            ->addColumn('investasi_ditahan', function (Summary $row) {
                return self::rupiah($row->investasi_ditahan);
            })
            ->orderColumn('investasi_ditahan', function (Builder $query, string $order) {
                $query->orderBy('summary.investasi_ditahan', $order);
            })
            ->filterColumn('investasi_ditahan', function (Builder $query, string $keyword) {
                self::filterDecimal($query, 'summary.investasi_ditahan', $keyword);
            })
            ->addColumn('total_investasi_transfer', function (Summary $row) {
                return self::rupiah($row->total_investasi_transfer);
            })
            ->orderColumn('total_investasi_transfer', function (Builder $query, string $order) {
                $query->orderBy('summary.total_investasi_transfer', $order);
            })
            ->filterColumn('total_investasi_transfer', function (Builder $query, string $keyword) {
                self::filterDecimal($query, 'summary.total_investasi_transfer', $keyword);
            })
            ->addColumn('total_transfer_investasi', function (Summary $row) {
                return self::rupiah($row->total_transfer_investasi);
            })
            ->orderColumn('total_transfer_investasi', function (Builder $query, string $order) {
                $query->orderBy('summary.total_transfer_investasi', $order);
            })
            ->filterColumn('total_transfer_investasi', function (Builder $query, string $keyword) {
                self::filterDecimal($query, 'summary.total_transfer_investasi', $keyword);
            })
            ->addColumn('margin_diterima', function (Summary $row) {
                return self::rupiah($row->margin_diterima);
            })
            ->orderColumn('margin_diterima', function (Builder $query, string $order) {
                $query->orderBy('summary.margin_diterima', $order);
            })
            ->filterColumn('margin_diterima', function (Builder $query, string $keyword) {
                self::filterDecimal($query, 'summary.margin_diterima', $keyword);
            })
            ->addColumn('margin_tersedia', function (Summary $row) {
                return self::rupiah($row->margin_tersedia);
            })
            ->orderColumn('margin_tersedia', function (Builder $query, string $order) {
                $query->orderBy('summary.margin_tersedia', $order);
            })
            ->filterColumn('margin_tersedia', function (Builder $query, string $keyword) {
                self::filterDecimal($query, 'summary.margin_tersedia', $keyword);
            })
            ->addColumn('margin_ditahan', function (Summary $row) {
                return self::rupiah($row->margin_ditahan);
            })
            ->orderColumn('margin_ditahan', function (Builder $query, string $order) {
                $query->orderBy('summary.margin_ditahan', $order);
            })
            ->filterColumn('margin_ditahan', function (Builder $query, string $keyword) {
                self::filterDecimal($query, 'summary.margin_ditahan', $keyword);
            })
            ->addColumn('total_margin', function (Summary $row) {
                return self::rupiah($row->total_margin);
            })
            ->orderColumn('total_margin', function (Builder $query, string $order) {
                $query->orderBy('summary.total_margin', $order);
            })
            ->filterColumn('total_margin', function (Builder $query, string $keyword) {
                self::filterDecimal($query, 'summary.total_margin', $keyword);
            })
            ->addColumn('sisa_margin', function (Summary $row) {
                return self::rupiah($row->sisa_margin);
            })
            ->orderColumn('sisa_margin', function (Builder $query, string $order) {
                $query->orderBy('summary.sisa_margin', $order);
            })
            ->filterColumn('sisa_margin', function (Builder $query, string $keyword) {
                self::filterDecimal($query, 'summary.sisa_margin', $keyword);
            })
            ->rawColumns([]);
    }
    public function query(Summary $model): \Illuminate\Support\Collection
    {
        $summary = Summary::first();

        if (! $summary) {
            return collect();
        }

        return Summary::with('investasi')
            ->where('id', $summary->id)
            ->get();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('summary-datatable')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'asc')        // default sort by tgl_investasi
            ->parameters([
                'dom'          => 'Bfrtip',
                'scrollX'      => true,
                'autoWidth'    => false,
                'searching'    => true,
                'initComplete' => $this->initCompleteJs(),
            ]);
    }

    protected function getColumns(): array
    {
        return [
            Column::make('tgl_investasi')
                ->title('Tgl. Investasi')
                ->addClass('text-center text-nowrap')
                ->searchable(true)
                ->orderable(true),

            Column::make('dana_tersedia')
                ->title('Dana Tersedia')
                ->addClass('text-end text-nowrap')
                ->searchable(true)
                ->orderable(true),

            Column::make('investasi_dikembalikan')
                ->title('Investasi Dikembalikan')
                ->addClass('text-end text-nowrap')
                ->searchable(true)
                ->orderable(true),

            Column::make('investasi_tambahan')
                ->title('Investasi Tambahan')
                ->addClass('text-end text-nowrap')
                ->searchable(true)
                ->orderable(true),

            Column::make('investasi_ditahan')
                ->title('Investasi Ditahan')
                ->addClass('text-end text-nowrap')
                ->searchable(true)
                ->orderable(true),

            Column::make('total_investasi_transfer')
                ->title('Total Investasi Transfer')
                ->addClass('text-end text-nowrap')
                ->searchable(true)
                ->orderable(true),

            Column::make('total_transfer_investasi')
                ->title('Total Transfer Investasi')
                ->addClass('text-end text-nowrap')
                ->searchable(true)
                ->orderable(true),

            Column::make('margin_diterima')
                ->title('Margin Diterima')
                ->addClass('text-end text-nowrap')
                ->searchable(true)
                ->orderable(true),

            Column::make('margin_tersedia')
                ->title('Margin Tersedia')
                ->addClass('text-end text-nowrap')
                ->searchable(true)
                ->orderable(true),

            Column::make('margin_ditahan')
                ->title('Margin Ditahan')
                ->addClass('text-end text-nowrap')
                ->searchable(true)
                ->orderable(true),

            Column::make('total_margin')
                ->title('Total Margin')
                ->addClass('text-end text-nowrap')
                ->searchable(true)
                ->orderable(true),

            Column::make('sisa_margin')
                ->title('Sisa Margin')
                ->addClass('text-end text-nowrap')
                ->searchable(true)
                ->orderable(true),
        ];
    }

    protected function initCompleteJs(): string
    {
        return <<<'JS'
                function () {
                    var api = this.api();
                
                    // Build a <tfoot> row with one <th> per column
                    var footerRow = $('<tr/>');
                    api.columns().every(function (colIdx) {
                        footerRow.append($('<th/>'));
                    });
                    $(api.table().node()).find('tfoot').length === 0
                        ? $(api.table().node()).append($('<tfoot/>').append(footerRow))
                        : $(api.table().node()).find('tfoot tr').replaceWith(footerRow);
                
                    // Inject inputs
                    api.columns().every(function (colIdx) {
                        var column = this;
                        var th     = $(column.footer());
                
                        if (colIdx === 0) {
                            // ── tgl_investasi → date picker ──────────────────────────────
                            var dateInput = $('<input>', {
                                type        : 'date',
                                class       : 'form-control form-control-sm',
                                placeholder : 'Filter tanggal…'
                            });
                            dateInput.appendTo(th.empty()).on('change', function () {
                                column.search(this.value).draw();
                            });
                        } else {
                            // ── decimal columns → text input ─────────────────────────────
                            var placeholders = [
                                '', // idx 0 handled above
                                'Dana Tersedia',
                                'Investasi Dikembalikan',
                                'Investasi Tambahan',
                                'Investasi Ditahan',
                                'Total Inv. Transfer',
                                'Total Transfer Inv.',
                                'Margin Diterima',
                                'Margin Tersedia',
                                'Margin Ditahan',
                                'Total Margin',
                                'Sisa Margin',
                            ];
                            var textInput = $('<input>', {
                                type        : 'text',
                                class       : 'form-control form-control-sm',
                                placeholder : placeholders[colIdx] || '…'
                            });
                            textInput.appendTo(th.empty()).on('keyup change clear', function () {
                                if (column.search() !== this.value) {
                                    column.search(this.value).draw();
                                }
                            });
                        }
                    });
                }
                JS;
    }

    protected static function rupiah(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    protected static function filterDecimal(Builder $query, string $column, string $keyword): void
    {
        $numeric = preg_replace('/[^0-9]/', '', $keyword);
        if ($numeric !== '') {
            $query->whereRaw("CAST({$column} AS CHAR) LIKE ?", ["%{$numeric}%"]);
        }
    }
}
