<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Menampilkan form login
     */
    public function showLoginForm()
    {
        if (session()->has('role')) {
            if (in_array(session('role'), ['admin', 'kasir'], true)) {
                return redirect()->route('admin.dashboard');
            } elseif (session('role') === 'siswa') {
                return redirect()->route('siswa.dashboard');
            }
        }

        return view('login');
    }

    /**
     * Memproses autentikasi login (pencocokan username & password sederhana)
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => 'Username wajib diisi!',
            'password.required' => 'Password wajib diisi!',
        ]);

        $user = User::where('username', $request->username)->first();

        if (! $user || ! $user->status || $user->password !== $request->password) {
            return back()->withInput()->with('error', 'Username atau password tidak cocok!');
        }

        // Set session
        session([
            'user_id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
        ]);

        Auth::login($user);

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard')->with('success', 'Selamat datang Admin!');
        } elseif ($user->role === 'kasir') {
            return redirect()->route('kasir.dashboard')->with('success', 'Selamat datang Kasir!');
        } else {
            return redirect()->route('siswa.dashboard')->with('success', 'Selamat datang, '.$user->username.'!');
        }
    }

    /**
     * Memproses logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Berhasil logout!');
    }
}
