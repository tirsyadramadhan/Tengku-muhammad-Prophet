<?php

namespace App\Http\Controllers;

use App\Exports\InvestasiExport;
use App\Models\Investasi;
use App\Models\Po;
use App\Models\Margin;
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
                ->addColumn('investasi_details', function ($row) {
                    $fmt = fn($val) => 'Rp ' . number_format($val, 0, ',', '.');

                    $modal_setor_awal  = $fmt($row->modal_setor_awal);
                    $modal_po_baru     = $fmt($row->modal_po_baru);
                    $margin            = $fmt($row->margin);
                    $pencairan_modal   = $fmt($row->pencairan_modal);
                    $margin_cair       = $fmt($row->margin_cair);
                    $pengembalian_dana = $fmt($row->pengembalian_dana);
                    $dana_tersedia     = $fmt($row->dana_tersedia);
                    $tgl               = Carbon::parse($row->tgl_investasi)->toIndonesianRelative();
                    $colorClass = fn($val) => ($val < 0)
                        ? 'text-danger'
                        : 'text-success';

                    return <<<HTML
                        <div class="card" style="
                            width: 260px;
                            background: #0f172a;
                            border: 1px solid #1e3a5f;
                            border-radius: 10px;
                            overflow: hidden;
                            font-family: 'Segoe UI', sans-serif;
                            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                        ">
                            <!-- Header -->
                            <div style="
                                background: linear-gradient(135deg, #1e40af, #0ea5e9);
                                padding: 8px 12px;
                                font-size: 11px;
                                font-weight: 700;
                                color: #fff;
                                letter-spacing: 0.08em;
                                text-transform: uppercase;
                            ">
                                Detail Investasi
                            </div>

                            <!-- Rows -->
                            <div style="padding: 6px 0;">

                                <div style="display:flex; justify-content:space-between; align-items:center; padding: 5px 12px; border-bottom: 1px solid #1e293b;">
                                    <span class="text-white" style="font-size:11px; color:#94a3b8; white-space:nowrap;">Modal Setor Awal</span>
                                    <span class="{$colorClass($row->modal_setor_awal)}" style="font-size:11px; font-weight:600; white-space:nowrap;">{$modal_setor_awal}</span>
                                </div>

                                <div style="display:flex; justify-content:space-between; align-items:center; padding: 5px 12px; border-bottom: 1px solid #1e293b;">
                                    <span class="text-white" style="font-size:11px; color:#94a3b8; white-space:nowrap;">Modal PO Baru</span>
                                    <span class="{$colorClass($row->modal_po_baru)}" style="font-size:11px; font-weight:600; white-space:nowrap;">{$modal_po_baru}</span>
                                </div>

                                <div style="display:flex; justify-content:space-between; align-items:center; padding: 5px 12px; border-bottom: 1px solid #1e293b;">
                                    <span class="text-white" style="font-size:11px; color:#94a3b8; white-space:nowrap;">Margin</span>
                                    <span class="{$colorClass($row->margin)}" style="font-size:11px; font-weight:600; white-space:nowrap;">{$margin}</span>
                                </div>

                                <div style="display:flex; justify-content:space-between; align-items:center; padding: 5px 12px; border-bottom: 1px solid #1e293b;">
                                    <span class="text-white" style="font-size:11px; color:#94a3b8; white-space:nowrap;">Pencairan Modal</span>
                                    <span class="{$colorClass($row->pencairan_modal)}" style="font-size:11px; font-weight:600; white-space:nowrap;">{$pencairan_modal}</span>
                                </div>

                                <div style="display:flex; justify-content:space-between; align-items:center; padding: 5px 12px; border-bottom: 1px solid #1e293b;">
                                    <span class="text-white" style="font-size:11px; color:#94a3b8; white-space:nowrap;">Margin Cair</span>
                                    <span class="{$colorClass($row->margin_cair)}" style="font-size:11px; font-weight:600; white-space:nowrap;">{$margin_cair}</span>
                                </div>

                                <div style="display:flex; justify-content:space-between; align-items:center; padding: 5px 12px; border-bottom: 1px solid #1e293b;">
                                    <span class="text-white" style="font-size:11px; color:#94a3b8; white-space:nowrap;">Pengembalian Dana</span>
                                    <span class="{$colorClass($row->pengembalian_dana)}" style="font-size:11px; font-weight:600; white-space:nowrap;">{$pengembalian_dana}</span>
                                </div>

                                <div style="display:flex; justify-content:space-between; align-items:center; padding: 5px 12px;">
                                    <span class="text-white" style="font-size:11px; color:#94a3b8; white-space:nowrap;">Dana Tersedia</span>
                                    <span class="{$colorClass($row->dana_tersedia)}" style="font-size:12px; font-weight:700; white-space:nowrap;">{$dana_tersedia}</span>
                                </div>

                            </div>

                            <!-- Footer -->
                            <div style="
                                background: #1e293b;
                                padding: 6px 12px;
                                font-size: 10px;
                                color: #fff;
                                display: flex;
                                gap: 5px;
                                flex-direction: column;
                            ">
                                <span class="badge bg-info" style="width: fit-content;">{$row->tgl_investasi}</span>
                                <span class="badge bg-primary" style="width: fit-content;">{$tgl}</span>
                            </div>
                        </div>
                        HTML;
                })
                ->orderColumn('investasi_details', function ($investments, $order) {
                    $investments->orderBy('dana_tersedia', $order);
                })

                ->filterColumn('investasi_details', function ($investments, $keyword) {
                    $keyword = trim($keyword);

                    // Strip "Rp " prefix and thousand separators to get raw numeric keyword
                    $numericKeyword = preg_replace('/[Rp\s\.]+/', '', $keyword);

                    // ── Date formats: 2026-07-25 or 25 Jul 2026
                    $dateFromISO = null;
                    $dateFromIndo = null;

                    // Match YYYY-MM-DD
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $keyword)) {
                        $dateFromISO = $keyword;
                    }

                    // Match "25 Jul 2026" or "25 July 2026"
                    if (preg_match('/^\d{1,2}\s+\w+\s+\d{4}$/', $keyword)) {
                        try {
                            $dateFromIndo = \Carbon\Carbon::parse($keyword)->format('Y-m-d');
                        } catch (\Exception $e) {
                            $dateFromIndo = null;
                        }
                    }

                    if ($dateFromISO || $dateFromIndo) {
                        $date = $dateFromISO ?? $dateFromIndo;
                        $investments->whereDate('tgl_investasi', $date);
                        return;
                    }

                    // ── Numeric / Rp keyword — search all money fields
                    if ($numericKeyword !== '') {
                        $investments->where(function ($q) use ($numericKeyword, $keyword) {
                            $q
                                // Raw numeric match
                                ->where('modal_setor_awal',  'like', "%{$numericKeyword}%")
                                ->orWhere('modal_po_baru',   'like', "%{$numericKeyword}%")
                                ->orWhere('margin',          'like', "%{$numericKeyword}%")
                                ->orWhere('pencairan_modal', 'like', "%{$numericKeyword}%")
                                ->orWhere('margin_cair',     'like', "%{$numericKeyword}%")
                                ->orWhere('pengembalian_dana', 'like', "%{$numericKeyword}%")
                                ->orWhere('dana_tersedia',   'like', "%{$numericKeyword}%")

                                // Formatted "Rp 900.000.000" match
                                ->orWhereRaw("REPLACE(FORMAT(modal_setor_awal,  0), ',', '.') like ?", ["%{$numericKeyword}%"])
                                ->orWhereRaw("REPLACE(FORMAT(modal_po_baru,    0), ',', '.') like ?", ["%{$numericKeyword}%"])
                                ->orWhereRaw("REPLACE(FORMAT(margin,           0), ',', '.') like ?", ["%{$numericKeyword}%"])
                                ->orWhereRaw("REPLACE(FORMAT(pencairan_modal,  0), ',', '.') like ?", ["%{$numericKeyword}%"])
                                ->orWhereRaw("REPLACE(FORMAT(margin_cair,      0), ',', '.') like ?", ["%{$numericKeyword}%"])
                                ->orWhereRaw("REPLACE(FORMAT(pengembalian_dana,0), ',', '.') like ?", ["%{$numericKeyword}%"])
                                ->orWhereRaw("REPLACE(FORMAT(dana_tersedia,    0), ',', '.') like ?", ["%{$numericKeyword}%"]);
                        });
                        return;
                    }

                    // ── Fallback: raw keyword against date
                    $investments->where('tgl_investasi', 'like', "%{$keyword}%");
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
            $rawColumns = ['investasi_details', 'action'];
            return $table->rawColumns($rawColumns)->make(true);
        }

        // ── STAT CARD TOTALS ────────────────────────────────────────────
        $totalMargin      = Investasi::sum('margin');
        $totalModalSetor  = Investasi::sum('modal_setor_awal');
        $totalPenarikan   = Investasi::sum('pengembalian_dana');
        $totalModalPoBaru = Investasi::sum('modal_po_baru');
        // Fetch the latest investment record based on id_investasi
        $investasi = Investasi::orderBy('id_investasi', 'desc')->first();

        // Initialize $prevDana to 0 in case no record exists
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

        $lastInvestasi = Investasi::orderBy('id_investasi', 'desc')->first();
        // Fetch the latest investment record based on id_investasi
        $investasi = Investasi::orderBy('id_investasi', 'desc')->first();

        // Initialize $prevDana to 0 in case no record exists
        $prevDana = 0;

        if ($investasi) {
            $prevDana = $investasi->dana_tersedia;
        }

        $marginTersedia = Margin::sum('margin_tersedia') - Po::where('status', '!=', 7)
            ->where('status', '!=', 0)
            ->sum('margin');
        $totalInvestasiTransfer = Investasi::sum('modal_setor_awal') - Investasi::sum('modal_po_baru') + Margin::sum('investasi_dikembalikan');
        $total_dana_ditransfer = $marginTersedia + $totalInvestasiTransfer;

        return view('investasi-create', compact('closedPos', 'prevDana', 'total_dana_ditransfer', 'marginTersedia', 'totalInvestasiTransfer'));
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
            // --- 1. Determine base values (positive sums) ---
            // Modal Setor
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

            // --- 2. Apply signs ---
            $signSetor = (int) ($request->sign_setor ?? 1);
            $valSetor = $valSetor * $signSetor;

            $signPoBaru = (int) ($request->sign_po_baru ?? 1);
            $valPoBaru = $valPoBaru * $signPoBaru;

            $signMargin = (int) ($request->sign_margin ?? 1);
            $valMargin = $valMargin * $signMargin;

            // --- 3. Previous Dana ---
            $lastInvestasi = Investasi::orderBy('id_investasi', 'desc')->first();
            $prevDana = $lastInvestasi ? $lastInvestasi->dana_tersedia : 0;

            // --- 4. User manual inputs (already signed by frontend toggles) ---
            $pencairan = (float) ($request->pencairan_modal ?? 0);
            $margin_cair = (float) ($request->margin_cair ?? 0);
            $pengembalian_dana = $pencairan + $margin_cair;

            // 2. CHANGE LOGIC: Sum the array values
            // If it's an array, sum it. If null, use 0.
            $rawPenarikan = $request->penarikan ?? [];
            $penarikan = is_array($rawPenarikan) ? array_sum($rawPenarikan) : (float) $rawPenarikan;
            // --- 5. Calculate Final Dana Tersedia ---
            $danaTersedia = ($prevDana + $valSetor + $valMargin + $pencairan) - ($valPoBaru + $penarikan);

            // --- 6. Create Record ---
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
