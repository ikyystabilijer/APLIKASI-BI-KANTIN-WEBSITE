<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Menampilkan dashboard admin beserta tabel CRUD menu dan daftar pesanan masuk
     */
    public function index()
    {
        $menus = Menu::orderBy('id', 'desc')->get();
        $pesanans = Pesanan::with(['user', 'menu'])->orderBy('id', 'desc')->get();

        $totalMenu = $menus->count();
        $totalPesanan = $pesanans->count();
        $pesananMenunggu = $pesanans->where('status', 'Menunggu Konfirmasi')->count();

        return view('admin.dashboard', compact('menus', 'pesanans', 'totalMenu', 'totalPesanan', 'pesananMenunggu'));
    }

    /**
     * Menyimpan menu baru ke tabel menu
     */
    public function storeMenu(Request $request)
    {
        $validated = $request->validate([
            'nama_menu' => 'required|string|max:100',
            'harga'     => 'required|numeric|min:0',
            'kategori'  => 'required|string|max:50',
        ], [
            'nama_menu.required' => 'Nama menu wajib diisi!',
            'harga.required'     => 'Harga wajib diisi!',
            'harga.numeric'      => 'Harga harus berupa angka!',
            'kategori.required'  => 'Kategori wajib diisi!',
        ]);

        Menu::create($validated);

        return redirect()->route('admin.dashboard')->with('success', 'Menu berhasil ditambahkan!');
    }

    /**
     * Memperbarui data menu di tabel menu
     */
    public function updateMenu(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_menu' => 'required|string|max:100',
            'harga'     => 'required|numeric|min:0',
            'kategori'  => 'required|string|max:50',
        ], [
            'nama_menu.required' => 'Nama menu wajib diisi!',
            'harga.required'     => 'Harga wajib diisi!',
            'harga.numeric'      => 'Harga harus berupa angka!',
            'kategori.required'  => 'Kategori wajib diisi!',
        ]);

        $menu = Menu::findOrFail($id);
        $menu->update($validated);

        return redirect()->route('admin.dashboard')->with('success', 'Menu berhasil diperbarui!');
    }

    /**
     * Menghapus menu dari tabel menu
     */
    public function destroyMenu($id)
    {
        $menu = Menu::findOrFail($id);
        
        // Hapus juga relasi pesanan jika ada, atau biarkan database handle
        $menu->pesanan()->delete();
        $menu->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Menu berhasil dihapus!');
    }

    /**
     * Mengelola / memperbarui status pesanan di tabel pesanan
     */
    public function updateStatusPesanan(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|max:100',
        ]);

        $pesanan = Pesanan::findOrFail($id);
        $pesanan->update([
            'status' => $request->status,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Status pesanan #' . $pesanan->id . ' berhasil diperbarui!');
    }
}
