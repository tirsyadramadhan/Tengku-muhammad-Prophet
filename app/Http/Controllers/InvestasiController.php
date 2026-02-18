<?php

namespace App\Http\Controllers;

use App\Models\Investasi;
use App\Models\Po;
use App\Models\Margin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class InvestasiController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // FIX 1: Remove ->orderBy('id_investasi', 'desc') here.
            // Let the Frontend DataTables 'order' parameter handle the sorting.
            $investments = Investasi::with('pos');

            return DataTables::of($investments)
                ->addIndexColumn()
                ->addColumn('pos_list', function ($inv) {
                    if ($inv->pos->count() > 0) {
                        $html = '';
                        foreach ($inv->pos as $po) {
                            if (empty($po->no_po)) {
                                $html .= '<span class="po-badge po-badge-danger"><i class="ri-error-warning-line me-1"></i> PO HAS NO PO NUMBER!</span>';
                            } else {
                                $html .= '<span class="po-badge" title="' . $po->nama_barang . '">' . $po->no_po . '</span>';
                            }
                        }
                        return $html;
                    }
                    return '<span class="badge bg-label-secondary">No PO Linked</span>';
                })
                ->editColumn('modal_setor_awal', fn($inv) => 'Rp ' . number_format($inv->modal_setor_awal))
                ->editColumn('modal_po_baru', fn($inv) => 'Rp ' . number_format($inv->modal_po_baru))
                ->editColumn('total_margin', fn($inv) => '<span class="text-success fw-semibold">+Rp ' . number_format($inv->total_margin) . '</span>')
                ->editColumn('pencairan_modal', fn($inv) => 'Rp ' . number_format($inv->pencairan_modal))
                ->editColumn('penarikan', fn($inv) => '<span class="text-danger">-Rp ' . number_format($inv->penarikan) . '</span>')
                ->editColumn('dana_tersedia', function ($inv) {
                    $badgeClass = $inv->dana_tersedia >= 0 ? 'bg-label-primary' : 'bg-label-danger';
                    return '<span class="badge ' . $badgeClass . ' fs-6">Rp ' . number_format($inv->dana_tersedia) . '</span>';
                })
                ->rawColumns(['pos_list', 'total_margin', 'penarikan', 'dana_tersedia'])
                ->make(true);
        }

        // Global totals for the stat cards
        $totalMargin       = Investasi::sum('total_margin');
        $totalModalSetor   = Investasi::sum('modal_setor_awal');
        $totalPenarikan    = Investasi::sum('dana_ditransfer');
        $totalModalPoBaru  = Investasi::sum('modal_po_baru');

        // You can keep the explicit order here for the stat card value, 
        // as this doesn't affect the table.
        $danaTersedia      = Investasi::sum('modal_setor_awal') - Investasi::sum('modal_po_baru') + Po::where('status', '!=', 0)->sum('margin') - Investasi::sum('penarikan') - Po::where('status', '!=', 7)
            ->where('status', '!=', 0)
            ->sum('margin');

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
        $prevDana = Investasi::sum('modal_setor_awal') - Investasi::sum('modal_po_baru') + Po::where('status', '!=', 0)->sum('margin') - Investasi::sum('penarikan') - Po::where('status', '!=', 7)
            ->where('status', '!=', 0)
            ->sum('margin');
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
            'tgl_manual'      => 'required',
            'ids_setor_awal'  => 'nullable|array',
            'ids_po_baru'     => 'nullable|array',
            'ids_margin'      => 'nullable|array',
            'manual_setor_awal' => 'numeric|min:0',
            'manual_po_baru'    => 'numeric|min:0',
            'manual_total_margin' => 'numeric|min:0',
            'pencairan_modal' => 'numeric|min:0',
            'penarikan'       => 'nullable|array',
            'penarikan.*'     => 'numeric',
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
                'tgl_investasi'    => $request->tgl_manual . '-01',
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
}
