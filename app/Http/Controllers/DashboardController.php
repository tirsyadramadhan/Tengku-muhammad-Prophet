<?php

namespace App\Http\Controllers;

use App\Models\Summary;

class DashboardController extends Controller
{
    public function showDashboard()
    {
        return view('dashboard');
    }

    public function getStats()
    {
        $summary = Summary::orderBy('id', 'desc')->first();

        if (!$summary) {
            return response()->json(['error' => 'Summary not found'], 404);
        }

        return response()->json([
            'dana_tersedia'             => $summary->dana_tersedia,
            'investasi_dikembalikan'    => $summary->investasi_dikembalikan,
            'investasi_tambahan'        => $summary->investasi_tambahan,
            'investasi_ditahan'         => $summary->investasi_ditahan,
            'total_investasi_transfer'  => $summary->total_investasi_transfer,
            'total_transfer_investasi'  => $summary->total_transfer_investasi,
            'margin_diterima'           => $summary->margin_diterima,
            'margin_tersedia'           => $summary->margin_tersedia,
            'margin_ditahan'            => $summary->margin_ditahan,
            'total_margin'              => $summary->total_margin,
            'sisa_margin'               => $summary->sisa_margin,
        ]);
    }

    public function danaTersedia() {}
}
