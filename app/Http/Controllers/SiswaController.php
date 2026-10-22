<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SiswaController extends Controller
{
    /**
     * Menampilkan katalog menu, keranjang belanja (session), dan riwayat pesanan siswa
     */
    public function index()
    {
        $userId = session('user_id') ?? Auth::id();

        $menus = Menu::orderBy('kategori', 'asc')->get();
        $pesanans = Pesanan::with('menu')
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();

        // Ambil keranjang belanja dari Session (Catatan Guru No. 10)
        $cart = session()->get('cart', []);
        $totalKeranjang = 0;
        foreach ($cart as $item) {
            $totalKeranjang += $item['subtotal'];
        }

        return view('siswa.dashboard', compact('menus', 'pesanans', 'cart', 'totalKeranjang'));
    }

    /**
     * Menambahkan menu ke keranjang belanja berbasis session (Catatan Guru No. 10)
     */
    public function tambahKeranjang(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menu,id',
            'jumlah'  => 'required|integer|min:1',
        ]);

        $menu = Menu::findOrFail($request->menu_id);
        $cart = session()->get('cart', []);

        if (isset($cart[$menu->id])) {
            $cart[$menu->id]['jumlah'] += $request->jumlah;
            $cart[$menu->id]['subtotal'] = $cart[$menu->id]['jumlah'] * $menu->harga;
        } else {
            $cart[$menu->id] = [
                'menu_id'   => $menu->id,
                'nama_menu' => $menu->nama_menu,
                'harga'     => $menu->harga,
                'jumlah'    => (int) $request->jumlah,
                'subtotal'  => $menu->harga * (int) $request->jumlah,
                'gambar'    => $menu->gambar,
                'kategori'  => $menu->kategori,
            ];
        }

        session()->put('cart', $cart);

        return redirect()->route('siswa.dashboard')->with('success', '✅ ' . $menu->nama_menu . ' berhasil ditambahkan ke keranjang!');
    }

    /**
     * Menghapus salah satu menu dari keranjang belanja session
     */
    public function hapusKeranjang($menuId)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$menuId])) {
            $namaMenu = $cart[$menuId]['nama_menu'];
            unset($cart[$menuId]);
            session()->put('cart', $cart);
            return redirect()->route('siswa.dashboard')->with('success', '🗑️ ' . $namaMenu . ' dihapus dari keranjang.');
        }

        return redirect()->route('siswa.dashboard');
    }

    /**
     * Mengosongkan seluruh isi keranjang
     */
    public function kosongkanKeranjang()
    {
        session()->forget('cart');
        return redirect()->route('siswa.dashboard')->with('success', 'Keranjang belanja telah dikosongkan.');
    }

    /**
     * Melakukan checkout keranjang belanja menggunakan DB Transaction (Catatan Guru No. 11)
     */
    public function checkoutKeranjang()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('siswa.dashboard')->with('error', 'Keranjang belanja Anda masih kosong!');
        }

        $userId = session('user_id') ?? Auth::id();

        // Menggunakan DB Transaction saat checkout pesanan
        DB::transaction(function () use ($cart, $userId) {
            foreach ($cart as $item) {
                Pesanan::create([
                    'user_id'     => $userId,
                    'menu_id'     => $item['menu_id'],
                    'jumlah'      => $item['jumlah'],
                    'total_harga' => $item['subtotal'],
                    'status'      => 'Menunggu Konfirmasi',
                ]);
            }

            // Hapus isi keranjang setelah sukses di-insert
            session()->forget('cart');
        });

        return redirect()->route('siswa.dashboard')->with('success', '🎉 Checkout berhasil! Pesanan Anda telah dikirim ke kantin.');
    }

    /**
     * Menangani proses pemesanan langsung (Quick Order) menggunakan DB Transaction
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

        // DB Transaction untuk menjamin integritas data (Catatan Guru No. 11)
        DB::transaction(function () use ($userId, $menu, $request, $totalHarga) {
            Pesanan::create([
                'user_id'     => $userId,
                'menu_id'     => $menu->id,
                'jumlah'      => $request->jumlah,
                'total_harga' => $totalHarga,
                'status'      => 'Menunggu Konfirmasi',
            ]);
        });

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
