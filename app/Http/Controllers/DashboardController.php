<?php

namespace App\Http\Controllers;

use App\Models\Po;
use App\Models\Margin;
use App\Models\Investasi;

class DashboardController extends Controller
{
    public function showDashboard()
    {
        return view('dashboard');
    }

    public function getStats()
    {
        $invSums = Investasi::query()
            ->selectRaw('SUM(modal_setor_awal) as total_awal, SUM(modal_po_baru) as total_po, SUM(margin_cair) as total_tarik, SUM(pengembalian_dana) as total_tf')
            ->first();

        $totalMargin = Po::where('status', '!=', 0)->sum('margin');
        $marginDitahan = Po::where('status', '!=', 0)->where('status', '!=', 8)->sum('margin');

        $marginSums = Margin::query()
            ->selectRaw('SUM(investasi_dikembalikan) as dikembalikan, SUM(margin_diterima) as diterima, SUM(margin_tersedia) as tersedia')
            ->first();

        $sisaMargin   = $totalMargin - $marginSums->diterima;
        $marginTersedia = $sisaMargin - $marginDitahan;
        $investasiDitahan = Po::where('status', '!=', 0)->where('status', '!=', 8)->sum('modal_awal') - 77334100;
        $totalInvestasiTransfer = $invSums->total_awal + $marginSums->dikembalikan - $investasiDitahan;
        return response()->json([
            'danaTersedia'          => $totalInvestasiTransfer + $marginTersedia,
            'totalDanaDitf'         => $invSums->total_tf,
            'investasiDikembalikan' => $marginSums->dikembalikan,
            'totalTfInvestasi'      => $invSums->total_awal,
            'marginDiterima'        => $marginSums->diterima,
            'totalMargin'           => $totalMargin,
            'sisaMargin'            => $sisaMargin,
            'marginTersedia'        => $marginTersedia,
            'investasiDitahan'      => $investasiDitahan,
            'marginDitahan'         => $marginDitahan,
            'totalInvestasiTransfer' => $totalInvestasiTransfer
        ]);
    }

    public function danaTersedia()
    {
        $invSums = Investasi::query()
            ->selectRaw('SUM(modal_setor_awal) as total_awal, SUM(modal_po_baru) as total_po, SUM(margin_cair) as total_tarik, SUM(pengembalian_dana) as total_tf')
            ->first();

        $totalMargin   = Po::where('status', '!=', 0)->sum('margin');
        $marginDitahan = Po::where('status', '!=', 0)->where('status', '!=', 8)->sum('margin');

        $marginSums = Margin::query()
            ->selectRaw('SUM(investasi_dikembalikan) as dikembalikan, SUM(margin_diterima) as diterima, SUM(margin_tersedia) as tersedia')
            ->first();

        $sisaMargin             = $totalMargin - $marginSums->diterima;
        $marginTersedia         = $sisaMargin - $marginDitahan;
        $investasiDitahan       = Po::where('status', '!=', 0)->where('status', '!=', 8)->sum('modal_awal') - 77334100;
        $totalInvestasiTransfer = $invSums->total_awal + $marginSums->dikembalikan - $investasiDitahan;
        $danaTersedia           = $totalInvestasiTransfer + $marginTersedia;

        $investasiRows = Investasi::all();

        $poMarginRows = Po::where('status', '!=', 0)->get();

        $poDitahanRows = Po::where('status', '!=', 0)->where('status', '!=', 8)->get();

        $marginRows = Margin::all();

        $investasiDikembalikan = $marginSums->dikembalikan;
        $marginDiterima = $marginSums->diterima;
        $modalSetorAwal = $invSums->total_awal;

        return view('dana-tersedia', compact(
            'danaTersedia',
            'invSums',
            'totalMargin',
            'marginDitahan',
            'sisaMargin',
            'marginTersedia',
            'investasiDitahan',
            'totalInvestasiTransfer',
            'marginSums',
            'investasiRows',
            'poMarginRows',
            'poDitahanRows',
            'marginRows',
            'investasiDikembalikan',
            'marginDiterima',
            'modalSetorAwal'
        ));
    }
}
