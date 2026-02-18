<?php

namespace App\Http\Controllers;

use App\Models\Investasi;
use App\Models\MarginDiterima;
use Illuminate\Http\Request;


class MarginDiterimaController extends Controller
{
    public function index()
    {
        $data = Investasi::get();
        return view('set-margin-diterima', compact('data'));
    }
    public function store(Request $request)
    {
        // 1. Validate 'margin_diterima' to match the 'name' attribute in your blade file
        $request->validate([
            'margin_diterima' => 'required',
        ]);

        // 2. Logic: If ID 1 exists, update it. If not, create it.
        // This uses the 'margin_diterima' column defined in your Model's $fillable array
        MarginDiterima::updateOrCreate(
            ['id' => 1], // The condition to find the record
            ['margin_diterima' => $request->margin_diterima] // The values to set
        );

        return redirect()->route('margin_diterima.index')->with('success', 'Margin updated successfully!');
    }
}
