<?php

namespace App\Http\Controllers;

use App\Models\Angkutan;

class AngkutanController extends Controller
{
    public function index()
    {
        $data = Angkutan::with(['customer', 'station'])
            ->orderBy('tanggal_keberangkatan')
            ->get();

        return view('angkutan.index', compact('data'));
    }
}