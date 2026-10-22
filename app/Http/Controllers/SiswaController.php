<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiswaController extends Controller
{
    /**
     * Menampilkan katalog menu dan daftar status pesanan siswa
     */
    public function index()
    {
        $userId = session('user_id') ?? Auth::id();

        $menus = Menu::orderBy('kategori', 'asc')->get();
        $pesanans = Pesanan::with('menu')
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();

        return view('siswa.dashboard', compact('menus', 'pesanans'));
    }

    /**
     * Menangani proses pemesanan makanan/minuman (insert ke tabel pesanan)
     */
    public function pesan(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menu,id',
            'jumlah'  => 'required|integer|min:1',
        ], [
            'menu_id.required' => 'Silakan pilih menu terlebih dahulu!',
            'menu_id.exists'   => 'Menu yang dipilih tidak valid!',
            'jumlah.required'  => 'Jumlah pesanan wajib diisi!',
            'jumlah.min'       => 'Jumlah pesanan minimal 1!',
        ]);

        $userId = session('user_id') ?? Auth::id();
        $menu = Menu::findOrFail($request->menu_id);
        $totalHarga = $menu->harga * $request->jumlah;

        Pesanan::create([
            'user_id'     => $userId,
            'menu_id'     => $menu->id,
            'jumlah'      => $request->jumlah,
            'total_harga' => $totalHarga,
            'status'      => 'Menunggu Konfirmasi',
        ]);

        return redirect()->route('siswa.dashboard')->with('success', 'Pesanan ' . $menu->nama_menu . ' (' . $request->jumlah . 'x) berhasil dipesan! Mohon tunggu konfirmasi kantin.');
    }

    /**
     * Endpoint JSON untuk cek status pesanan secara real-time
     */
    public function getStatusPesanan()
    {
        $userId = session('user_id') ?? Auth::id();

        $pesanans = Pesanan::with('menu')
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($pesanans);
    }
}
