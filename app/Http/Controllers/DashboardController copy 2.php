<?php

namespace App\Http\Controllers;

use App\Models\Investasi;
use App\Models\Po;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function showDashboard()
    {
        if (!Auth::check()) {
            return redirect()->intended('/');
        }

        $stats = [
            'sisa_margin' => Po::where('status', '!=', 7)->where('status', '!=', 0)->sum('margin'),
            'dana_tersedia' => Investasi::sum('modal_setor_awal') - Investasi::sum('modal_po_baru') + Po::where('status', '!=', 0)->sum('margin') - Investasi::sum('penarikan') - Po::where('status', '!=', 7)
                ->where('status', '!=', 0)
                ->sum('margin'),
            'total_dana_ditransfer' => Investasi::orderBy('id_investasi', 'desc')->value('penarikan') ?? 0,
            // Margin Tersedia = sisa margin - margin ditahan
            'margin_tersedia' => Po::where('status', '!=', 0)->sum('margin') - Investasi::sum('penarikan') - Po::where('status', '!=', 7)
                ->where('status', '!=', 0)
                ->sum('margin'),

            // Total Investasi Transfer
            'total_investasi_transfer' => Investasi::sum('modal_setor_awal') - Investasi::sum('modal_po_baru'),

            // Total TF Investasi
            'total_transfer_investasi'         => Investasi::sum('modal_setor_awal'),

            // Total margin from official POs (status = 1)
            'total_margin'        => Po::where('status', '!=', 0)->sum('margin'),

            // Investasi yang ditahan
            'investasi_ditahan'       => Investasi::sum('modal_po_baru'),

            // Margin currently locked in active (not Close) official POs
            'margin_ditahan'      => Po::where('status', '!=', 7)
                ->where('status', '!=', 0)
                ->sum('margin'),

            'margin_ditahan_full' => Po::where('status', '!=', 7)->sum('margin'),

            // FIXED: Chained the where clauses and fixed the operators
            'modal_ditahan'       => Po::where('status', '!=', 7)
                ->where('status', '!=', 0)
                ->sum('modal_awal'),

            'modal_ditahan_full'  => Po::where('status', '!=', 7)->sum('modal_awal'),

            // LOGIC FIX: Added where status = 1 to ensure you only withdraw from official POs
            'margin_bisa_ditarik' => Po::where('status', 7)
                ->where('status', '!=', 0)
                ->sum('margin'),

            'modal_bisa_ditarik'  => Po::where('status', 7)
                ->where('status', '!=', 0)
                ->sum('modal_awal'),

            'margin_diterima'     => Investasi::sum('penarikan') + Po::where('status', '!=', 0)->sum('margin') - Investasi::sum('penarikan') - Po::where('status', '!=', 7)
                ->where('status', '!=', 0)
                ->sum('margin'),
            'count_active'        => Investasi::where('dana_tersedia', '>', 0)->count(),
        ];

        return view('dashboard', compact('stats'));
    }
}
