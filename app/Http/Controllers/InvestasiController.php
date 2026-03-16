<?php

namespace App\Http\Controllers;

use App\Exports\InvestasiExport;
use App\Models\Investasi;
use App\Models\Po;
use App\Models\Summary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
use App\Imports\InvestasiImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class InvestasiController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $investments = Investasi::query();
            $user = Auth::user();

            $table = DataTables::of($investments)
                ->addIndexColumn()
                ->addColumn('modal_setor_awal', function ($row) {
                    if (!$row->modal_setor_awal && $row->modal_setor_awal !== 0) {
                        return '<span style="font-size:0.75rem;color:#94a3b8;font-weight:500;">
                                    <i class="ri-minus-line me-1"></i>Tidak ada
                                </span>';
                    }
                    $formatted = 'Rp ' . number_format($row->modal_setor_awal, 0, ',', '.');
                    return '<span style="display:inline-flex;align-items:center;gap:5px;
                                        font-size:0.8rem;font-weight:700;color:#1e293b;white-space:nowrap;">
                                <i class="ri-money-rupee-circle-line" style="color:#16a34a;font-size:0.9rem;"></i>
                                ' . $formatted . '
                            </span>';
                })
                ->orderColumn('modal_setor_awal', 'tbl_investasi.modal_setor_awal $1')
                ->filterColumn('modal_setor_awal', function ($query, $keyword) {
                    $clean = str_replace(',', '.', preg_replace('/[Rp\s.]/', '', $keyword));
                    if (is_numeric($clean)) {
                        $query->where('tbl_investasi.modal_setor_awal', (float) $clean);
                    } else {
                        $query->where('tbl_investasi.modal_setor_awal', 'like', "%{$keyword}%");
                    }
                })
                ->addColumn('modal_po_baru', function ($row) {
                    if (!$row->modal_po_baru && $row->modal_po_baru !== 0) {
                        return '<span style="font-size:0.75rem;color:#94a3b8;font-weight:500;">
                                    <i class="ri-minus-line me-1"></i>Tidak ada
                                </span>';
                    }
                    $formatted = 'Rp ' . number_format($row->modal_po_baru, 0, ',', '.');
                    return '<span style="display:inline-flex;align-items:center;gap:5px;
                                        font-size:0.8rem;font-weight:700;color:#1e293b;white-space:nowrap;">
                                <i class="ri-money-rupee-circle-line" style="color:#16a34a;font-size:0.9rem;"></i>
                                ' . $formatted . '
                            </span>';
                })
                ->orderColumn('modal_po_baru', 'tbl_investasi.modal_po_baru $1')
                ->filterColumn('modal_po_baru', function ($query, $keyword) {
                    $clean = str_replace(',', '.', preg_replace('/[Rp\s.]/', '', $keyword));
                    if (is_numeric($clean)) {
                        $query->where('tbl_investasi.modal_po_baru', (float) $clean);
                    } else {
                        $query->where('tbl_investasi.modal_po_baru', 'like', "%{$keyword}%");
                    }
                })
                ->addColumn('margin', function ($row) {
                    if (!$row->margin && $row->margin !== 0) {
                        return '<span style="font-size:0.75rem;color:#94a3b8;font-weight:500;">
                                    <i class="ri-minus-line me-1"></i>Tidak ada
                                </span>';
                    }
                    $formatted = 'Rp ' . number_format($row->margin, 0, ',', '.');
                    $color     = $row->margin >= 0 ? '#16a34a' : '#dc2626';
                    $icon      = $row->margin >= 0 ? 'ri-arrow-up-line' : 'ri-arrow-down-line';
                    return '<span style="display:inline-flex;align-items:center;gap:5px;
                                        font-size:0.8rem;font-weight:700;color:' . $color . ';white-space:nowrap;">
                                <i class="' . $icon . '" style="font-size:0.9rem;"></i>
                                ' . $formatted . '
                            </span>';
                })
                ->orderColumn('margin', 'tbl_investasi.margin $1')
                ->filterColumn('margin', function ($query, $keyword) {
                    $clean = str_replace(',', '.', preg_replace('/[Rp\s.]/', '', $keyword));
                    if (is_numeric($clean)) {
                        $query->where('tbl_investasi.margin', (float) $clean);
                    } else {
                        $query->where('tbl_investasi.margin', 'like', "%{$keyword}%");
                    }
                })
                ->addColumn('pencairan_modal', function ($row) {
                    if (!$row->pencairan_modal && $row->pencairan_modal !== 0) {
                        return '<span style="font-size:0.75rem;color:#94a3b8;font-weight:500;">
                                    <i class="ri-minus-line me-1"></i>Tidak ada
                                </span>';
                    }
                    $formatted = 'Rp ' . number_format($row->pencairan_modal, 0, ',', '.');
                    return '<span style="display:inline-flex;align-items:center;gap:5px;
                                        font-size:0.8rem;font-weight:700;color:#1e293b;white-space:nowrap;">
                                <i class="ri-money-rupee-circle-line" style="color:#0284c7;font-size:0.9rem;"></i>
                                ' . $formatted . '
                            </span>';
                })
                ->orderColumn('pencairan_modal', 'tbl_investasi.pencairan_modal $1')
                ->filterColumn('pencairan_modal', function ($query, $keyword) {
                    $clean = str_replace(',', '.', preg_replace('/[Rp\s.]/', '', $keyword));
                    if (is_numeric($clean)) {
                        $query->where('tbl_investasi.pencairan_modal', (float) $clean);
                    } else {
                        $query->where('tbl_investasi.pencairan_modal', 'like', "%{$keyword}%");
                    }
                })
                ->addColumn('margin_cair', function ($row) {
                    if (!$row->margin_cair && $row->margin_cair !== 0) {
                        return '<span style="font-size:0.75rem;color:#94a3b8;font-weight:500;">
                                    <i class="ri-minus-line me-1"></i>Tidak ada
                                </span>';
                    }
                    $formatted = 'Rp ' . number_format($row->margin_cair, 0, ',', '.');
                    $color     = $row->margin_cair >= 0 ? '#16a34a' : '#dc2626';
                    $icon      = $row->margin_cair >= 0 ? 'ri-arrow-up-line' : 'ri-arrow-down-line';
                    return '<span style="display:inline-flex;align-items:center;gap:5px;
                                        font-size:0.8rem;font-weight:700;color:' . $color . ';white-space:nowrap;">
                                <i class="' . $icon . '" style="font-size:0.9rem;"></i>
                                ' . $formatted . '
                            </span>';
                })
                ->orderColumn('margin_cair', 'tbl_investasi.margin_cair $1')
                ->filterColumn('margin_cair', function ($query, $keyword) {
                    $clean = str_replace(',', '.', preg_replace('/[Rp\s.]/', '', $keyword));
                    if (is_numeric($clean)) {
                        $query->where('tbl_investasi.margin_cair', (float) $clean);
                    } else {
                        $query->where('tbl_investasi.margin_cair', 'like', "%{$keyword}%");
                    }
                })
                ->addColumn('pengembalian_dana', function ($row) {
                    if (is_null($row->pengembalian_dana)) {
                        return '<span style="font-size:0.75rem;color:#94a3b8;font-weight:500;">
                                    <i class="ri-minus-line me-1"></i>Tidak ada
                                </span>';
                    }
                    $formatted = 'Rp ' . number_format($row->pengembalian_dana, 0, ',', '.');
                    return '<span style="display:inline-flex;align-items:center;gap:5px;
                                        font-size:0.8rem;font-weight:700;color:#1e293b;white-space:nowrap;">
                                <i class="ri-refund-line" style="color:#d97706;font-size:0.9rem;"></i>
                                ' . $formatted . '
                            </span>';
                })
                ->orderColumn('pengembalian_dana', 'tbl_investasi.pengembalian_dana $1')
                ->filterColumn('pengembalian_dana', function ($query, $keyword) {
                    $clean = str_replace(',', '.', preg_replace('/[Rp\s.]/', '', $keyword));
                    if (is_numeric($clean)) {
                        $query->where('tbl_investasi.pengembalian_dana', (float) $clean);
                    } else {
                        $query->where('tbl_investasi.pengembalian_dana', 'like', "%{$keyword}%");
                    }
                })
                ->addColumn('dana_tersedia', function ($row) {
                    if (!$row->dana_tersedia && $row->dana_tersedia !== 0) {
                        return '<span style="font-size:0.75rem;color:#94a3b8;font-weight:500;">
                                    <i class="ri-minus-line me-1"></i>Tidak ada
                                </span>';
                    }
                    $formatted = 'Rp ' . number_format($row->dana_tersedia, 0, ',', '.');
                    $color     = $row->dana_tersedia > 0 ? '#16a34a' : '#dc2626';
                    return '<span style="display:inline-flex;align-items:center;gap:5px;
                                        background:' . ($row->dana_tersedia > 0 ? '#f0fdf4' : '#fef2f2') . ';
                                        color:' . $color . ';
                                        border:1px solid ' . $color . '30;border-radius:8px;
                                        padding:3px 9px;font-size:0.78rem;font-weight:700;white-space:nowrap;">
                                <i class="ri-wallet-3-line" style="font-size:0.85rem;"></i>
                                ' . $formatted . '
                            </span>';
                })
                ->orderColumn('dana_tersedia', 'tbl_investasi.dana_tersedia $1')
                ->filterColumn('dana_tersedia', function ($query, $keyword) {
                    $clean = str_replace(',', '.', preg_replace('/[Rp\s.]/', '', $keyword));
                    if (is_numeric($clean)) {
                        $query->where('tbl_investasi.dana_tersedia', (float) $clean);
                    } else {
                        $query->where('tbl_investasi.dana_tersedia', 'like', "%{$keyword}%");
                    }
                })
                ->addColumn('tgl_investasi', function ($row) {
                    if (!$row->tgl_investasi) {
                        return '<span style="font-size:0.75rem;color:#94a3b8;font-weight:500;">
                                    <i class="ri-minus-line me-1"></i>Tidak ada
                                </span>';
                    }
                    $date    = \Carbon\Carbon::parse($row->tgl_investasi);
                    $dateStr = $date->translatedFormat('d M Y');
                    return '<span style="display:inline-flex;align-items:center;gap:5px;
                                        font-size:0.78rem;font-weight:600;color:#1e293b;white-space:nowrap;">
                                <i class="ri-calendar-check-line" style="color:#0284c7;font-size:0.85rem;"></i>
                                ' . $dateStr . '
                            </span>';
                })
                ->orderColumn('tgl_investasi', 'tbl_investasi.tgl_investasi $1')
                ->filterColumn('tgl_investasi', function ($query, $keyword) {
                    try {
                        $date = \Carbon\Carbon::createFromFormat('d M Y', trim($keyword));
                        $query->whereDate('tbl_investasi.tgl_investasi', $date->toDateString());
                    } catch (\Exception $e) {
                        try {
                            $date = \Carbon\Carbon::parse($keyword);
                            $query->whereDate('tbl_investasi.tgl_investasi', $date->toDateString());
                        } catch (\Exception $e2) {
                            $query->whereRaw(
                                "DATE_FORMAT(tbl_investasi.tgl_investasi, '%d %b %Y') LIKE ?",
                                ["%{$keyword}%"]
                            );
                        }
                    }
                });
            $table->addColumn('action', function ($inv) {
                $user = Auth::user();

                if ($user && $user->role_id === 2) {
                    return ''; // return empty for role 2
                }

                $deleteUrl = Route::has('investments.destroy') ? route('investments.destroy', $inv->id_investasi) : '#';
                return '
                    <button type="button" class="btn btn-sm btn-icon btn-label-danger btn-delete" 
                        data-url="' . $deleteUrl . '" 
                        title="Delete">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                    ';
            });
            $rawColumns = ['modal_setor_awal', 'modal_po_baru', 'margin', 'pencairan_modal', 'margin_cair', 'pengembalian_dana', 'dana_tersedia', 'tgl_investasi', 'action'];
            return $table->rawColumns($rawColumns)->make(true);
        }

        $totalMargin      = Investasi::sum('margin');
        $totalModalSetor  = Investasi::sum('modal_setor_awal');
        $totalPenarikan   = Investasi::sum('pengembalian_dana');
        $totalModalPoBaru = Investasi::sum('modal_po_baru');
        $investasi = Investasi::orderBy('id_investasi', 'desc')->first();

        $danaTersedia = 0;

        if ($investasi) {
            $danaTersedia = $investasi->dana_tersedia;
        }
        return view('investasi-index', compact(
            'totalMargin',
            'totalModalSetor',
            'totalPenarikan',
            'totalModalPoBaru',
            'danaTersedia'
        ));
    }

    public function create()
    {
        $closedPos = Po::where('status', Po::STATUS_CLOSED)
            ->select('po_id', 'no_po', 'nama_barang', 'modal_awal', 'margin')
            ->orderBy('no_po', 'desc')
            ->get();

        $summary = Summary::first();

        $dana_tersedia = $summary['dana_tersedia'];
        $marginTersedia = $summary['margin_tersedia'];
        $totalInvestasiTransfer = $summary['total_investasi_transfer'];
        $dana_ditransfer = $marginTersedia + $totalInvestasiTransfer;

        return view('investasi-create', compact('closedPos', 'dana_tersedia', 'marginTersedia', 'totalInvestasiTransfer', 'dana_ditransfer'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids_setor_awal'  => 'nullable|array',
            'ids_po_baru'     => 'nullable|array',
            'ids_margin'      => 'nullable|array',
            'manual_setor_awal' => 'nullable|numeric|min:0',
            'manual_po_baru'    => 'nullable|numeric|min:0',
            'manual_total_margin' => 'nullable|numeric|min:0',
            'pencairan_modal' => 'nullable|numeric|min:0',
            'penarikan'       => 'nullable|array',
            'penarikan.*'     => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();
            $valSetor = 0;
            if ($request->mode_setor === 'manual') {
                $valSetor = (float) $request->manual_setor_awal;
            } else {
                if (!empty($request->ids_setor_awal)) {
                    $valSetor = Po::whereIn('po_id', $request->ids_setor_awal)->sum('modal_awal');
                }
            }

            // Modal PO Baru
            $valPoBaru = 0;
            if ($request->mode_po_baru === 'manual') {
                $valPoBaru = (float) $request->manual_po_baru;
            } else {
                if (!empty($request->ids_po_baru)) {
                    $valPoBaru = Po::whereIn('po_id', $request->ids_po_baru)->sum('modal_awal');
                }
            }

            // Margin
            $valMargin = 0;
            if ($request->mode_margin === 'manual') {
                $valMargin = (float) $request->manual_total_margin;
            } else {
                if (!empty($request->ids_margin)) {
                    $valMargin = Po::whereIn('po_id', $request->ids_margin)->sum('margin');
                }
            }

            $signSetor = (int) ($request->sign_setor ?? 1);
            $valSetor = $valSetor * $signSetor;

            $signPoBaru = (int) ($request->sign_po_baru ?? 1);
            $valPoBaru = $valPoBaru * $signPoBaru;

            $signMargin = (int) ($request->sign_margin ?? 1);
            $valMargin = $valMargin * $signMargin;

            $pencairan = (float) ($request->pencairan_modal ?? 0);
            $margin_cair = (float) ($request->margin_cair ?? 0);
            $pengembalian_dana = $pencairan + $margin_cair;
            $dana_tersedia = (float) ($request->dana_tersedia);

            $rawPenarikan = $request->penarikan ?? [];
            $penarikan = is_array($rawPenarikan) ? array_sum($rawPenarikan) : (float) $rawPenarikan;
            $danaTersedia = ($dana_tersedia + $valSetor + $valMargin + $pencairan) - ($valPoBaru + $penarikan);

            $investasi = Investasi::create([
                'tgl_investasi'    => Carbon::now(),
                'modal_setor_awal' => $valSetor,
                'modal_po_baru'    => $valPoBaru,
                'margin'     => $valMargin,
                'pencairan_modal'  => $pencairan,
                'margin_cair'        => $margin_cair,
                'pengembalian_dana'        => $pengembalian_dana,
                'dana_tersedia'    => $danaTersedia,
            ]);

            $summary = Summary::first();
            $investasi_dikembalikan = $summary->total_investasi_transfer >= 0 ? $summary->investasi_dikembalikan + $summary->total_investasi_transfer : $summary->investasi_dikembalikan;
            $total_transfer_investasi = $summary->total_investasi_transfer <= 0
                ? $summary->total_transfer_investasi + $summary->total_investasi_transfer
                : $summary->total_transfer_investasi;
            $margin_diterima = $summary->margin_diterima + $summary->margin_tersedia;
            $investasi_ditahan = Po::where('status', '!=', 0)->where('status', '!=', 8)->sum('modal_awal');
            $margin_ditahan = Po::where('status', '!=', 0)->where('status', '!=', 8)->sum('margin');
            $total_margin = Po::where('status', '!=', 0)->sum('margin');
            $sisa_margin   = $total_margin - $margin_diterima;

            Summary::create([
                'investasi_id' => $investasi->id_investasi,
                'dana_tersedia' => $request->dana_tersedia,
                'investasi_dikembalikan' => $investasi_dikembalikan,
                'investasi_tambahan' => $request->filled('investasi_tambahan') && $request->investasi_tambahan != 0
                    ? $request->investasi_tambahan
                    : $summary->investasi_tambahan,
                'investasi_ditahan' => $investasi_ditahan,
                'total_investasi_transfer' => 0,
                'total_transfer_investasi' => $total_transfer_investasi,
                'margin_diterima' => $margin_diterima,
                'margin_tersedia' => 0,
                'margin_ditahan' => $margin_ditahan,
                'total_margin' => $total_margin,
                'sisa_margin' => $sisa_margin,
            ]);

            $this->logCreate($investasi);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Investasi Created. Dana Tersedia: ' . number_format($danaTersedia, 2),
                'redirect_url' => route('investments.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function export()
    {
        $this->logExport();
        return Excel::download(new InvestasiExport, 'investasi.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file'        => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new InvestasiImport, $request->file('file'));
            $this->logImport();
            return response()->json(['success' => true, 'message' => 'Data berhasil diimport.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function importForm()
    {
        return view("investasi-import");
    }

    public function destroy($id_investasi)
    {
        try {
            $investasi = Investasi::findOrFail($id_investasi);

            $this->logDelete($investasi, $investasi, 'Investasi dengan dana Rp ' . number_format($investasi->dana_tersedia, 0, ',', '.') . ' di hapus');

            $investasi->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Investasi berhasil di hapus'
            ]);
        } catch (\Illuminate\Database\QueryException $e) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem.'
            ], 500);
        }
    }

    public function getStats()
    {
        $totalMargin      = Investasi::sum('margin');
        $totalModalSetor  = Investasi::sum('modal_setor_awal');
        $totalPenarikan   = Investasi::sum('pengembalian_dana');
        $totalModalPoBaru = Investasi::sum('modal_po_baru');
        $investasi = Investasi::orderBy('id_investasi', 'desc')->first();
        $danaTersedia = 0;
        if ($investasi) {
            $danaTersedia = $investasi->dana_tersedia;
        }
        return response()->json([
            'totalMargin'      => (float) $totalMargin,
            'totalModalSetor'  => (float) $totalModalSetor,
            'totalModalPoBaru' => (float) $totalModalPoBaru,
            'totalPenarikan'   => (float) $totalPenarikan,
            'danaTersedia'     => (float) $danaTersedia,
        ]);
    }

    public function truncate(Request $request)
    {
        $request->validate([
            'confirm' => ['required', 'string', function ($attribute, $value, $fail) {
                if ($value !== "SAYA YAKIN ATAS TINDAKAN INI") {
                    $fail('Konfirmasi tidak sesuai. Ketik tepat: SAYA YAKIN ATAS TINDAKAN INI');
                }
            }],
        ]);

        try {
            DB::table('tbl_investasi')->truncate();

            return response()->json([
                'success' => true,
                'message' => 'Semua data investasi berhasil dikosongkan.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengosongkan data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
