<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Http\Request;

class KasirController extends Controller
{
    public function index()
    {
        $pesanans = Pesanan::with(['user', 'menu'])->orderByDesc('id')->get();

        return view('kasir.dashboard', compact('pesanans'));
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Menunggu Konfirmasi,Sedang Diproses,Siap Diambil,Selesai,Dibatalkan',
        ]);

        Pesanan::findOrFail($id)->update(['status' => $validated['status']]);

        return redirect()->route('kasir.dashboard')->with('success', 'Status pesanan berhasil diperbarui.');
    }
}
