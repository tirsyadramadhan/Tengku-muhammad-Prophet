// Route::get('/api/dashboard-stats', function () {
  //   return response()->json([
  //     'danaTersedia' => Investasi::sum('modal_setor_awal') - Investasi::sum('modal_po_baru') + Po::where('status', '!=', 0)->sum('margin') - Investasi::sum('penarikan') - Po::where('status', '!=', 7)
  //       ->where('status', '!=', 0)
  //       ->sum('margin'),
  //     'totalDanaDitf'    => Investasi::sum('dana_ditransfer'),
  //     'investasiDikembalikan'  => Margin::sum('investasi_dikembalikan'),
  //     'totalTfInvestasi'   => Investasi::sum('modal_setor_awal'),
  //     'marginDiterima'   => Margin::sum('margin_diterima'),
  //     'totalMargin'   => Po::where('status', '!=', 0)->sum('margin'),
  //     'sisaMargin'   => Po::where('status', '!=', 0)->sum('margin') - Margin::sum('margin_diterima'),
  //     'marginTersedia'   => Margin::sum('margin_tersedia') - Po::where('status', '!=', 7)
  //       ->where('status', '!=', 0)
  //       ->sum('margin'),
  //     'investasiDitahan'   => Investasi::sum('modal_po_baru'),
  //     'marginDitahan'   => Po::where('status', '!=', 7)
  //       ->where('status', '!=', 0)
  //       ->sum('margin'),
  //   ]);
  // })->name('api.dashboard.stats');

    Route::get('/api/incomingPo-stats', function () {
    return response()->json([
      'incoming' => Po::where('status', 0)->count(),
      'price'    => Po::where('status', 0)->sum('total'),
      'capital'  => Po::where('status', 0)->sum('modal_awal'),
      'margin'   => Po::where('status', 0)->sum('margin'),
    ]);
  })->name('api.incomingPo.stats');
  Route::get('/api/po-stats', function () {
    return response()->json([
      'incoming' => Po::where('status', '!=', 0)->count(),
      'price'    => Po::where('status', '!=', 0)->sum('total'),
      'capital'  => Po::where('status', '!=', 0)->sum('modal_awal'),
      'margin'   => Po::where('status', '!=', 0)->sum('margin'),
    ]);
  })->name('api.po.stats');
