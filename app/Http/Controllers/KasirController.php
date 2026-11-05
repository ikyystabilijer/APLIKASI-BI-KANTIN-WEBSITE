<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Http\Request;

class KasirController extends Controller
{
    /**
     * Menampilkan dashboard kasir dengan filter dan ringkasan pembayaran
     */
    public function index(Request $request)
    {
        $query = Pesanan::with(['user', 'menu'])->orderByDesc('id');

        // Filter Status Pembayaran
        if ($request->filled('status_pembayaran')) {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }

        // Filter Metode Pembayaran (Cash / QRIS)
        if ($request->filled('metode_pembayaran')) {
            $query->where('metode_pembayaran', $request->metode_pembayaran);
        }

        // Filter Status Pesanan
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Pencarian Siswa / ID Pesanan
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('username', 'like', '%'.$search.'%');
                    });
            });
        }

        $pesanans = $query->get();

        // Statistik Ringkasan Kasir
        $totalPesanan = Pesanan::count();
        $totalBelumDibayar = Pesanan::where('status_pembayaran', 'Belum Dibayar')->count();
        $totalSudahDibayar = Pesanan::where('status_pembayaran', 'Sudah Dibayar')->count();
        $totalCash = Pesanan::where('metode_pembayaran', 'Cash')->count();
        $totalQris = Pesanan::where('metode_pembayaran', 'QRIS')->count();
        $pendapatanTervalidasi = Pesanan::where('status_pembayaran', 'Sudah Dibayar')
            ->where('status', '!=', 'Dibatalkan')
            ->sum('total_harga');

        return view('kasir.dashboard', compact(
            'pesanans',
            'totalPesanan',
            'totalBelumDibayar',
            'totalSudahDibayar',
            'totalCash',
            'totalQris',
            'pendapatanTervalidasi'
        ));
    }

    /**
     * Memvalidasi pembayaran pesanan (Cash / QRIS) oleh kasir
     */
    public function validasiPembayaran(Request $request, $id)
    {
        $request->validate([
            'status_pembayaran' => 'nullable|in:Belum Dibayar,Sudah Dibayar',
        ]);

        $pesanan = Pesanan::findOrFail($id);
        $statusPembayaran = $request->input('status_pembayaran', 'Sudah Dibayar');

        $updateData = [
            'status_pembayaran' => $statusPembayaran,
        ];

        // Jika pesanan masih menunggu konfirmasi dan pembayaran divalidasi, otomatis lanjut ke Sedang Diproses
        if ($statusPembayaran === 'Sudah Dibayar' && $pesanan->status === 'Menunggu Konfirmasi') {
            $updateData['status'] = 'Sedang Diproses';
        }

        $pesanan->update($updateData);

        $pesan = $statusPembayaran === 'Sudah Dibayar'
            ? '✅ Pembayaran pesanan #'.$pesanan->id.' ('.$pesanan->metode_pembayaran.') berhasil divalidasi!'
            : 'ℹ️ Validasi pembayaran pesanan #'.$pesanan->id.' telah dibatalkan (Belum Dibayar).';

        return redirect()->back()->with('success', $pesan);
    }

    /**
     * Memperbarui status pesanan dan status pembayaran (opsional)
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Menunggu Konfirmasi,Sedang Diproses,Siap Diambil,Selesai,Dibatalkan',
            'status_pembayaran' => 'nullable|in:Belum Dibayar,Sudah Dibayar',
        ]);

        $pesanan = Pesanan::findOrFail($id);
        $pesanan->update($validated);

        return redirect()->back()->with('success', 'Pesanan #'.$pesanan->id.' berhasil diperbarui.');
    }
}
