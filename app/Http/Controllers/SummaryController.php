<?php

namespace App\Http\Controllers;

use App\Models\Summary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

class SummaryController extends Controller
{
    protected static function rupiah(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    protected static function filterDecimal($query, string $column, string $keyword): void
    {
        $numeric = preg_replace('/[^0-9]/', '', $keyword);
        if ($numeric !== '') {
            $query->whereRaw("CAST({$column} AS CHAR) LIKE ?", ["%{$numeric}%"]);
        }
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $summary = Summary::join('tbl_investasi', 'summary.investasi_id', '=', 'tbl_investasi.id_investasi')
                ->select('summary.*', 'tbl_investasi.tgl_investasi');

            $table = DataTables::of($summary)
                ->addIndexColumn()
                ->addColumn('tgl_investasi', function (Summary $row) {
                    return $row->tgl_investasi
                        ? Carbon::parse($row->tgl_investasi)->translatedFormat('d M Y')
                        : '-';
                })
                ->orderColumn('tgl_investasi', function ($query, string $order) {
                    $query->orderBy('tbl_investasi.tgl_investasi', $order);
                })
                ->filterColumn('tgl_investasi', function ($query, string $keyword) {
                    if ($keyword) {
                        $query->whereDate('tbl_investasi.tgl_investasi', $keyword);
                    }
                })
                ->addColumn('dana_tersedia', function (Summary $row) {
                    return self::rupiah($row->dana_tersedia);
                })
                ->orderColumn('dana_tersedia', function ($query, string $order) {
                    $query->orderBy('summary.dana_tersedia', $order);
                })
                ->filterColumn('dana_tersedia', function ($query, string $keyword) {
                    self::filterDecimal($query, 'summary.dana_tersedia', $keyword);
                })
                ->addColumn('investasi_dikembalikan', function (Summary $row) {
                    return self::rupiah($row->investasi_dikembalikan);
                })
                ->orderColumn('investasi_dikembalikan', function ($query, string $order) {
                    $query->orderBy('summary.investasi_dikembalikan', $order);
                })
                ->filterColumn('investasi_dikembalikan', function ($query, string $keyword) {
                    self::filterDecimal($query, 'summary.investasi_dikembalikan', $keyword);
                })
                ->addColumn('investasi_tambahan', function (Summary $row) {
                    return self::rupiah($row->investasi_tambahan);
                })
                ->orderColumn('investasi_tambahan', function ($query, string $order) {
                    $query->orderBy('summary.investasi_tambahan', $order);
                })
                ->filterColumn('investasi_tambahan', function ($query, string $keyword) {
                    self::filterDecimal($query, 'summary.investasi_tambahan', $keyword);
                })
                ->addColumn('investasi_ditahan', function (Summary $row) {
                    return self::rupiah($row->investasi_ditahan);
                })
                ->orderColumn('investasi_ditahan', function ($query, string $order) {
                    $query->orderBy('summary.investasi_ditahan', $order);
                })
                ->filterColumn('investasi_ditahan', function ($query, string $keyword) {
                    self::filterDecimal($query, 'summary.investasi_ditahan', $keyword);
                })
                ->addColumn('total_investasi_transfer', function (Summary $row) {
                    return self::rupiah($row->total_investasi_transfer);
                })
                ->orderColumn('total_investasi_transfer', function ($query, string $order) {
                    $query->orderBy('summary.total_investasi_transfer', $order);
                })
                ->filterColumn('total_investasi_transfer', function ($query, string $keyword) {
                    self::filterDecimal($query, 'summary.total_investasi_transfer', $keyword);
                })
                ->addColumn('total_transfer_investasi', function (Summary $row) {
                    return self::rupiah($row->total_transfer_investasi);
                })
                ->orderColumn('total_transfer_investasi', function ($query, string $order) {
                    $query->orderBy('summary.total_transfer_investasi', $order);
                })
                ->filterColumn('total_transfer_investasi', function ($query, string $keyword) {
                    self::filterDecimal($query, 'summary.total_transfer_investasi', $keyword);
                })
                ->addColumn('margin_diterima', function (Summary $row) {
                    return self::rupiah($row->margin_diterima);
                })
                ->orderColumn('margin_diterima', function ($query, string $order) {
                    $query->orderBy('summary.margin_diterima', $order);
                })
                ->filterColumn('margin_diterima', function ($query, string $keyword) {
                    self::filterDecimal($query, 'summary.margin_diterima', $keyword);
                })
                ->addColumn('margin_tersedia', function (Summary $row) {
                    return self::rupiah($row->margin_tersedia);
                })
                ->orderColumn('margin_tersedia', function ($query, string $order) {
                    $query->orderBy('summary.margin_tersedia', $order);
                })
                ->filterColumn('margin_tersedia', function ($query, string $keyword) {
                    self::filterDecimal($query, 'summary.margin_tersedia', $keyword);
                })
                ->addColumn('margin_ditahan', function (Summary $row) {
                    return self::rupiah($row->margin_ditahan);
                })
                ->orderColumn('margin_ditahan', function ($query, string $order) {
                    $query->orderBy('summary.margin_ditahan', $order);
                })
                ->filterColumn('margin_ditahan', function ($query, string $keyword) {
                    self::filterDecimal($query, 'summary.margin_ditahan', $keyword);
                })
                ->addColumn('total_margin', function (Summary $row) {
                    return self::rupiah($row->total_margin);
                })
                ->orderColumn('total_margin', function ($query, string $order) {
                    $query->orderBy('summary.total_margin', $order);
                })
                ->filterColumn('total_margin', function ($query, string $keyword) {
                    self::filterDecimal($query, 'summary.total_margin', $keyword);
                })
                ->addColumn('sisa_margin', function (Summary $row) {
                    return self::rupiah($row->sisa_margin);
                })
                ->orderColumn('sisa_margin', function ($query, string $order) {
                    $query->orderBy('summary.sisa_margin', $order);
                })
                ->filterColumn('sisa_margin', function ($query, string $keyword) {
                    self::filterDecimal($query, 'summary.sisa_margin', $keyword);
                });

            $table->addColumn('action', function ($row) {
                $user = Auth::user();

                if ($user && $user->role_id === 2) {
                    return ''; // return empty for role 2
                }

                $deleteUrl = Route::has('summary.destroy') ? route('summary.destroy', $row->id) : '#';
                return '
                    <button type="button" class="btn btn-sm btn-icon btn-label-danger btn-delete" 
                        data-url="' . $deleteUrl . '" 
                        title="Delete">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                    ';
            });
            $rawColumns = ['action'];
            return $table->rawColumns($rawColumns)->make(true);
        }
        return view('summary');
    }

    public function destroy($id)
    {
        try {
            $summary = Summary::findOrFail($id);

            $this->logDelete($summary, $summary, 'Summary dengan dana Rp ' . number_format($summary->dana_tersedia, 0, ',', '.') . ' di hapus');

            $summary->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Summary berhasil di hapus'
            ]);
        } catch (\Illuminate\Database\QueryException $e) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem.'
            ], 500);
        }
    }
}
