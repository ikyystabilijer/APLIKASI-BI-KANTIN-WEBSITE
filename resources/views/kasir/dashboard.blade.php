<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir - BI-Kantin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <span class="navbar-brand fw-bold">BI-Kantin <span class="badge bg-warning text-dark">Kasir</span></span>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="btn btn-outline-light btn-sm">Logout</button>
        </form>
    </div>
</nav>
<div class="container py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card shadow-sm">
        <div class="card-header fw-bold">Validasi Pembayaran dan Pesanan</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>ID</th><th>Siswa</th><th>Menu</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($pesanans as $pesanan)
                    <tr>
                        <td>#{{ $pesanan->id }}</td>
                        <td>{{ $pesanan->user->username ?? '-' }}</td>
                        <td>{{ $pesanan->menu->nama_menu ?? 'Menu dihapus' }} ({{ $pesanan->jumlah }}x)</td>
                        <td>Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
                        <td>{{ $pesanan->status }}</td>
                        <td>
                            <form action="{{ route('kasir.pesanan.status', $pesanan->id) }}" method="POST" class="d-flex gap-2">
                                @csrf
                                @method('PUT')
                                <select name="status" class="form-select form-select-sm">
                                    @foreach(['Menunggu Konfirmasi', 'Sedang Diproses', 'Siap Diambil', 'Selesai', 'Dibatalkan'] as $status)
                                        <option value="{{ $status }}" @selected($pesanan->status === $status)>{{ $status }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-primary btn-sm">Simpan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada pesanan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
