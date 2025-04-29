<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product; 

class BerandaController extends Controller
{
    public function berandaBackend()
    {
        return view('backend.v_beranda.index', [
            'judul' => 'Halaman Beranda',
        ]);
    }

    public function index()
    {
        $produk = Product::where('status', 1)->orderBy('updated_at', 'desc')->paginate(6);
        return view('v_beranda.index', [
            'judul' => 'Halan Beranda',
            'produk' => $produk,
        ]);
    }
}
