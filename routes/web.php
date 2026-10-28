<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\SiswaController;
use Illuminate\Support\Facades\Route;

// Redirect root ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// Autentikasi (Login & Logout)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Rute Halaman Admin
Route::prefix('admin')->middleware('role:admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard', [AdminController::class, 'index']);

    // CRUD Menu
    Route::post('/menu', [AdminController::class, 'storeMenu'])->name('admin.menu.store');
    Route::put('/menu/{id}', [AdminController::class, 'updateMenu'])->name('admin.menu.update');
    Route::delete('/menu/{id}', [AdminController::class, 'destroyMenu'])->name('admin.menu.destroy');

    // CRUD akun pengguna
    Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');

    // Pengelolaan Status Pesanan
    Route::put('/pesanan/{id}/status', [AdminController::class, 'updateStatusPesanan'])->name('admin.pesanan.status');
});

Route::prefix('kasir')->middleware('role:kasir')->group(function () {
    Route::get('/', [KasirController::class, 'index'])->name('kasir.dashboard');
    Route::put('/pesanan/{id}/status', [KasirController::class, 'updateStatus'])->name('kasir.pesanan.status');
});

// Rute Halaman Siswa
Route::prefix('siswa')->middleware('role:siswa')->group(function () {
    Route::get('/', [SiswaController::class, 'index'])->name('siswa.dashboard');
    Route::get('/dashboard', [SiswaController::class, 'index']);

    // Keranjang Belanja Berbasis Session (Catatan Guru No. 10)
    Route::post('/keranjang', [SiswaController::class, 'tambahKeranjang'])->name('siswa.cart.add');
    Route::delete('/keranjang/{id}', [SiswaController::class, 'hapusKeranjang'])->name('siswa.cart.remove');
    Route::post('/keranjang/kosongkan', [SiswaController::class, 'kosongkanKeranjang'])->name('siswa.cart.clear');

    // Checkout Keranjang dengan DB Transaction (Catatan Guru No. 11)
    Route::post('/checkout', [SiswaController::class, 'checkoutKeranjang'])->name('siswa.cart.checkout');

    // Proses Pemesanan Langsung
    Route::post('/pesan', [SiswaController::class, 'pesan'])->name('siswa.pesan');

    // Cek Status Pesanan Real-time (JSON API)
    Route::get('/status-pesanan', [SiswaController::class, 'getStatusPesanan'])->name('siswa.pesanan.status');
});
