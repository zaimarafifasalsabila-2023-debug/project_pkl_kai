<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Angkutan;
use App\Models\Customer;
use App\Models\Station;

class DashboardController extends Controller
{
    public function index()
    {
        $totalAngkutan = Angkutan::count();
        $totalCustomer = Customer::count();
        $totalStation = Station::count();
        
        return view('dashboard.index', compact('totalAngkutan', 'totalCustomer', 'totalStation'));
    }

    public function inputData()
    {
        return view('dashboard.input-data');
    }

    public function previewData()
    {
        $data = Angkutan::with(['customer', 'station'])
            ->orderBy('tanggal_keberangkatan', 'desc')
            ->get();
            
        return view('dashboard.preview-data', compact('data'));
    }

    public function statistik()
    {
        // Data untuk statistik
        $monthlyData = Angkutan::selectRaw('MONTH(tanggal_keberangkatan) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
            
        $stationData = Angkutan::with('station')
            ->get()
            ->groupBy('station.nama_stasiun')
            ->map(function($item) {
                return $item->count();
            });
            
        return view('dashboard.statistik', compact('monthlyData', 'stationData'));
    }
}
