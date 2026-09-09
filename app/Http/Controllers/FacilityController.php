<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller publik untuk katalog dan jadwal fasilitas (Poin 5, 6, 7).
 */
class FacilityController extends Controller
{
    /**
     * Menampilkan daftar fasilitas dengan filter (q, tipe, lokasi, kapasitas_min).
     */
    public function index(Request $request): View
    {
        return view('fasilitas.index');
    }

    /**
     * Menampilkan detail fasilitas umum (tanpa data pemohon).
     */
    public function show(Facility $facility): View
    {
        return view('fasilitas.show', ['facility' => $facility]);
    }

    /**
     * Menampilkan jadwal ketersediaan slot fasilitas.
     */
    public function jadwal(Request $request, Facility $facility): View
    {
        return view('fasilitas.jadwal', ['facility' => $facility]);
    }
}
