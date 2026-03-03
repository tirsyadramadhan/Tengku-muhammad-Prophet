<?php

namespace App\Http\Controllers;

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

class InvestasiController extends Controller
{
    // ============================================================
    // REPLACE your entire index() method with this
    // ============================================================

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $investments = Investasi::get();

            return DataTables::of($investments)
                ->addIndexColumn()

                // ── MONEY COLUMNS ───────────────────────────────────────
                ->editColumn('modal_setor_awal', fn($inv) =>
                'Rp ' . number_format($inv->modal_setor_awal, 0, ',', '.'))

                ->editColumn('modal_po_baru', fn($inv) =>
                'Rp ' . number_format($inv->modal_po_baru, 0, ',', '.'))

                ->editColumn('margin', fn($inv) =>
                '<span style="color:#2e7d32; font-weight:600;">+Rp '
                    . number_format($inv->margin, 0, ',', '.')
                    . '</span>')

                ->editColumn('pencairan_modal', fn($inv) =>
                'Rp ' . number_format($inv->pencairan_modal, 0, ',', '.'))

                ->editColumn('margin_cair', fn($inv) =>
                '<span style="color:#ff3e1d; font-weight:600;">-Rp '
                    . number_format($inv->margin_cair, 0, ',', '.')
                    . '</span>')

                ->editColumn('pengembalian_dana', function ($inv) {
                    $color = $inv->pengembalian_dana >= 0 ? '#696cff' : '#ff3e1d';
                    return '<span style="color:' . $color . '; font-weight:800;">'
                        . 'Rp ' . number_format($inv->pengembalian_dana, 0, ',', '.')
                        . '</span>';
                })

                ->editColumn('dana_tersedia', function ($inv) {
                    $color = $inv->dana_tersedia >= 0 ? '#696cff' : '#ff3e1d';
                    return '<span style="color:' . $color . '; font-weight:800;">'
                        . 'Rp ' . number_format($inv->dana_tersedia, 0, ',', '.')
                        . '</span>';
                })
                ->rawColumns([
                    'margin',
                    'margin_cair',
                    'pengembalian_dana',
                    'dana_tersedia',
                ])
                ->make(true);
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
            $marginDiterima = Po::where('status', 7)->sum('margin');
            $totalMargin = Po::where('status', '!=', 0)->sum('margin');
            Margin::query()->update([
                'margin_tersedia' => $totalMargin
                    - $marginDiterima,
                'margin_diterima' => $marginDiterima,
                'investasi_dikembalikan' => - (Investasi::sum('modal_setor_awal') - Investasi::sum('modal_po_baru'))
            ]);

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

            // Total Margin
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
                'total_margin'     => $valMargin,
                'pencairan_modal'  => $pencairan,
                'penarikan'        => $penarikan,
                'dana_ditransfer'        => $penarikan,
                'dana_tersedia'    => $danaTersedia,
            ]);

            // --- 7. Attach POs to Pivot ---
            $allIds = array_unique(array_merge(
                $request->ids_setor_awal ?? [],
                $request->ids_po_baru ?? [],
                $request->ids_margin ?? []
            ));

            if (!empty($allIds)) {
                $investasi->pos()->sync($allIds);
            }

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
    public function import(Request $request)
    {
        $request->validate([
            'file'        => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new InvestasiImport, $request->file('file'));
            return response()->json(['success' => true, 'message' => 'Data berhasil diimport.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function importForm()
    {
        return view("investasi-import");
    }
}
