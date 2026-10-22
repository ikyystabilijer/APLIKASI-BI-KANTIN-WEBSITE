<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Menampilkan dashboard admin, statistik (termasuk pesanan hari ini), CRUD menu, dan monitoring pesanan masuk
     */
    public function index()
    {
        $menus = Menu::orderBy('id', 'desc')->get();
        $pesanans = Pesanan::with(['user', 'menu'])->orderBy('id', 'desc')->get();

        $totalMenu = $menus->count();
        $totalPesanan = $pesanans->count();
        $pesananMenunggu = $pesanans->where('status', 'Menunggu Konfirmasi')->count();

        // Ringkasan pesanan masuk hari ini (Catatan Guru No. 12)
        $pesananHariIni = Pesanan::whereDate('created_at', today())->count();
        $pendapatanHariIni = Pesanan::whereDate('created_at', today())
            ->where('status', '!=', 'Dibatalkan')
            ->sum('total_harga');

        return view('admin.dashboard', compact(
            'menus',
            'pesanans',
            'totalMenu',
            'totalPesanan',
            'pesananMenunggu',
            'pesananHariIni',
            'pendapatanHariIni'
        ));
    }

    /**
     * Menyimpan menu baru beserta validasi upload gambar (Catatan Guru No. 9)
     */
    public function storeMenu(Request $request)
    {
        $validated = $request->validate([
            'nama_menu' => 'required|string|max:100',
            'harga'     => 'required|numeric|min:0',
            'kategori'  => 'required|string|max:50',
            'gambar'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'nama_menu.required' => 'Nama menu wajib diisi!',
            'harga.required'     => 'Harga wajib diisi!',
            'harga.numeric'      => 'Harga harus berupa angka!',
            'kategori.required'  => 'Kategori wajib diisi!',
            'gambar.image'       => 'File yang diunggah harus berupa gambar!',
            'gambar.mimes'       => 'Format gambar harus jpeg, png, jpg, atau webp!',
            'gambar.max'         => 'Ukuran gambar maksimal 2MB!',
        ]);

        $data = [
            'nama_menu' => $validated['nama_menu'],
            'harga'     => $validated['harga'],
            'kategori'  => $validated['kategori'],
        ];

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('menu', 'public');
        }

        Menu::create($data);

        return redirect()->route('admin.dashboard')->with('success', 'Menu baru berhasil ditambahkan!');
    }

    /**
     * Memperbarui data menu dan mengganti gambar bila ada upload baru
     */
    public function updateMenu(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_menu' => 'required|string|max:100',
            'harga'     => 'required|numeric|min:0',
            'kategori'  => 'required|string|max:50',
            'gambar'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'nama_menu.required' => 'Nama menu wajib diisi!',
            'harga.required'     => 'Harga wajib diisi!',
            'harga.numeric'      => 'Harga harus berupa angka!',
            'kategori.required'  => 'Kategori wajib diisi!',
            'gambar.image'       => 'File yang diunggah harus berupa gambar!',
            'gambar.mimes'       => 'Format gambar harus jpeg, png, jpg, atau webp!',
            'gambar.max'         => 'Ukuran gambar maksimal 2MB!',
        ]);

        $menu = Menu::findOrFail($id);
        $data = [
            'nama_menu' => $validated['nama_menu'],
            'harga'     => $validated['harga'],
            'kategori'  => $validated['kategori'],
        ];

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($menu->gambar && Storage::disk('public')->exists($menu->gambar)) {
                Storage::disk('public')->delete($menu->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('menu', 'public');
        }

        $menu->update($data);

        return redirect()->route('admin.dashboard')->with('success', 'Menu berhasil diperbarui!');
    }

    /**
     * Menghapus menu dari tabel menu beserta gambarnya
     */
    public function destroyMenu($id)
    {
        $menu = Menu::findOrFail($id);

        if ($menu->gambar && Storage::disk('public')->exists($menu->gambar)) {
            Storage::disk('public')->delete($menu->gambar);
        }

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
