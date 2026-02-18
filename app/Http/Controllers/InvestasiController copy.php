<?php

namespace App\Http\Controllers;

use App\Models\Investasi;
use App\Models\Po;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class InvestasiController extends Controller
{
    public function index()
    {
        $totalMargin      = Investasi::sum('total_margin');
        $totalModalSetor  = Investasi::sum('modal_setor_awal');
        $totalPenarikan   = Investasi::sum('penarikan');
        $totalModalPoBaru = Investasi::sum('modal_po_baru');
        $totalDanaTersedia = Investasi::sum('dana_tersedia');

        $investments = Investasi::with('pos')->orderBy('id_investasi', 'desc')->paginate(10);

        return view('investasi-index', compact(
            'investments',
            'totalMargin',
            'totalModalSetor',
            'totalPenarikan',
            'totalModalPoBaru',
            'totalDanaTersedia'
        ));
    }

    public function create(Request $request)
    {
        // AJXA Request for DataTables
        if ($request->ajax()) {
            // FIX: Only fetch POs with status 'Closed' (Status 3 equivalent in logic)
            // Also filter out POs that are already attached to other investments if necessary, 
            // but for now, we just get Closed ones.
            $data = Po::select(['po_id', 'no_po', 'nama_barang', 'modal_awal', 'margin', 'status'])
                ->where('status', 3);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" 
                        name="po_ids[]" 
                        value="' . $row->po_id . '" 
                        class="po-checkbox form-check-input"
                        data-modal="' . $row->modal_awal . '"
                        data-margin="' . $row->margin . '">';
                })
                ->editColumn('modal_awal', function ($row) {
                    return number_format($row->modal_awal, 2);
                })
                ->editColumn('margin', function ($row) {
                    return number_format($row->margin, 2);
                })
                ->rawColumns(['checkbox'])
                ->make(true);
        }

        $prevInvestments = Investasi::orderBy('id_investasi', 'desc')->limit(20)->get();
        return view('investasi-create', compact('prevInvestments'));
    }

    public function store(Request $request)
    {
        // 1. Validation
        $validator = Validator::make($request->all(), [
            'tgl_manual'       => 'required|date',
            'po_ids'           => 'nullable|array',
            'po_ids.*'         => 'exists:tbl_po,po_id',
            'modal_setor_awal' => 'numeric|min:0',
            'modal_po_baru'    => 'numeric|min:0',
            'total_margin'     => 'numeric|min:0',
            'pencairan_modal'  => 'numeric|min:0',
            'penarikan'        => 'numeric|min:0',
            'dana_tersedia'    => 'numeric', // Can be negative
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $investasi = Investasi::create([
                'modal_setor_awal' => $request->modal_setor_awal ?? 0,
                'modal_po_baru'    => $request->modal_po_baru ?? 0,
                'total_margin'     => $request->total_margin ?? 0,
                'pencairan_modal'  => $request->pencairan_modal ?? 0,
                'penarikan'        => $request->penarikan ?? 0,
                'dana_tersedia'    => $request->dana_tersedia ?? 0,
                'tgl_investasi'    => $request->tgl_manual,
            ]);

            if ($request->has('po_ids')) {
                $investasi->pos()->sync($request->po_ids);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data Investasi berhasil disimpan dengan ' . count($request->po_ids ?? []) . ' PO terkait.',
                'redirect_url' => route('investments.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }
}
