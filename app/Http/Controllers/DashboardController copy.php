<?php

namespace App\Http\Controllers;

use App\Models\Investasi;
use App\Models\MarginDiterima;
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
            'total_modal'     => Investasi::sum('modal_setor_awal'),
            'total_margin'    => Po::sum('margin'),
            'total_po_baru'   => Investasi::sum('modal_po_baru'),
            'margin_ditahan'  => Po::where('status', '!=', 'Close')->sum('margin'),
            'modal_ditahan'  => Po::where('status', '!=', 'Close')->sum('modal_awal'),
            'margin_bisa_ditarik'  => Po::where('status', 'Close')->sum('margin'),
            'modal_bisa_ditarik'  => Po::where('status', 'Close')->sum('modal_awal'),
            'margin_diterima'  =>    MarginDiterima::sum('margin_diterima'),
            'count_active'    => Investasi::where('dana_tersedia', '>', 0)->count(),
        ];

        $investments = Investasi::with(['pos.customer'])
            ->orderBy('tgl_investasi', 'desc')
            ->paginate(10);

        return view('dashboard', compact('investments', 'stats'));
    }
}
