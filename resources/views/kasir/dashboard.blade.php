<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir - Validasi Pembayaran - BI-Kantin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05);
        }
        .table th {
            font-weight: 600;
            background-color: #f1f5f9;
        }
        .qris-box {
            background: #ffffff;
            border: 2px solid #dc3545;
            border-radius: 0.75rem;
            padding: 1rem;
            display: inline-block;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.15);
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
        <span class="navbar-brand fw-bold">
            <i class="bi bi-shop me-1 text-warning"></i> BI-Kantin <span class="badge bg-warning text-dark fs-6">Kasir</span>
        </span>
        <div class="d-flex align-items-center gap-2">
            <!-- Tombol Tampilkan QRIS Kasir -->
            <button type="button" class="btn btn-outline-warning btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalQrisKasir">
                <i class="bi bi-qr-code me-1"></i> QRIS Kasir
            </button>

            <span class="text-white small ms-2 d-none d-md-inline">
                <i class="bi bi-person-badge me-1"></i> {{ session('username', 'Kasir') }}
            </span>

            <form action="{{ route('logout') }}" method="POST" class="d-inline ms-1">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </button>
            </form>
        </div>
    </div>
</nav>

<div class="container py-4">
    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Statistic Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3 bg-white border-start border-warning border-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">BELUM DIBAYAR</div>
                        <h3 class="fw-bold mb-0 text-warning">{{ $totalBelumDibayar }}</h3>
                        <small class="text-muted">Perlu validasi kasir</small>
                    </div>
                    <i class="bi bi-hourglass-split display-6 text-warning opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3 bg-white border-start border-success border-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">SUDAH DIBAYAR</div>
                        <h3 class="fw-bold mb-0 text-success">{{ $totalSudahDibayar }}</h3>
                        <small class="text-muted">Tervalidasi</small>
                    </div>
                    <i class="bi bi-check-circle-fill display-6 text-success opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3 bg-white border-start border-info border-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">TRANSAKSI METODE</div>
                        <h4 class="fw-bold mb-0 text-dark">
                            <span class="text-danger">{{ $totalQris }} QRIS</span> / <span class="text-secondary">{{ $totalCash }} Cash</span>
                        </h4>
                        <small class="text-muted">Total: {{ $totalPesanan }} Pesanan</small>
                    </div>
                    <i class="bi bi-credit-card-2-front display-6 text-info opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3 bg-white border-start border-primary border-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">TOTAL PENDAPATAN TERVALIDASI</div>
                        <h4 class="fw-bold mb-0 text-primary">Rp {{ number_format($pendapatanTervalidasi, 0, ',', '.') }}</h4>
                        <small class="text-muted">Pembayaran sah</small>
                    </div>
                    <i class="bi bi-cash-coin display-6 text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Tabel Validasi Pembayaran dan Pesanan -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="card-title mb-0 fw-bold text-dark">
                    <i class="bi bi-cash-stack text-success me-2"></i>Validasi Pembayaran & Pesanan Kasir
                </h5>
                <small class="text-muted">Validasi pembayaran Cash / QRIS dan perbarui status pengerjaan pesanan</small>
            </div>
            <span class="badge bg-secondary">{{ count($pesanans) }} Pesanan Ditampilkan</span>
        </div>

        <!-- Filter & Pencarian -->
        <div class="card-body border-bottom bg-light bg-opacity-50">
            <form method="GET" action="{{ route('kasir.dashboard') }}" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="search" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Cari ID atau nama siswa...">
                </div>
                <div class="col-md-3">
                    <select name="status_pembayaran" class="form-select form-select-sm">
                        <option value="">Semua Status Pembayaran</option>
                        <option value="Belum Dibayar" @selected(request('status_pembayaran') === 'Belum Dibayar')>Belum Dibayar (Menunggu)</option>
                        <option value="Sudah Dibayar" @selected(request('status_pembayaran') === 'Sudah Dibayar')>Sudah Dibayar (Lunas)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="metode_pembayaran" class="form-select form-select-sm">
                        <option value="">Semua Metode</option>
                        <option value="Cash" @selected(request('metode_pembayaran') === 'Cash')>Cash (Tunai)</option>
                        <option value="QRIS" @selected(request('metode_pembayaran') === 'QRIS')>QRIS</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status Pesanan</option>
                        @foreach(['Menunggu Konfirmasi', 'Sedang Diproses', 'Siap Diambil', 'Selesai', 'Dibatalkan'] as $st)
                            <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'status_pembayaran', 'metode_pembayaran', 'status']))
                        <a href="{{ route('kasir.dashboard') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 70px;">ID</th>
                        <th>Waktu</th>
                        <th>Siswa</th>
                        <th>Menu & Qty</th>
                        <th>Total</th>
                        <th>Metode</th>
                        <th>Status Pembayaran</th>
                        <th>Validasi Kasir</th>
                        <th class="pe-3" style="min-width: 200px;">Status Pesanan</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($pesanans as $pesanan)
                    <tr>
                        <td class="ps-3 fw-bold">#{{ $pesanan->id }}</td>
                        <td class="text-muted small">
                            {{ $pesanan->created_at ? $pesanan->created_at->format('d/m H:i') : '-' }}
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-person me-1"></i>{{ $pesanan->user->username ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $pesanan->menu->nama_menu ?? 'Menu dihapus' }}</span>
                            <span class="badge bg-secondary ms-1">{{ $pesanan->jumlah }}x</span>
                        </td>
                        <td class="text-success fw-bold">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
                        <td>
                            @if(strtoupper($pesanan->metode_pembayaran) === 'QRIS')
                                <span class="badge bg-danger text-white"><i class="bi bi-qr-code me-1"></i> QRIS</span>
                            @else
                                <span class="badge bg-secondary text-white"><i class="bi bi-cash me-1"></i> Cash</span>
                            @endif
                        </td>
                        <td>
                            @if($pesanan->status_pembayaran === 'Sudah Dibayar')
                                <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Sudah Dibayar</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Belum Dibayar</span>
                            @endif
                        </td>
                        <td>
                            @if($pesanan->status_pembayaran === 'Sudah Dibayar')
                                <div class="d-flex align-items-center gap-1">
                                    <span class="text-success small fw-bold text-nowrap"><i class="bi bi-patch-check-fill me-1"></i> Tervalidasi</span>
                                    <form action="{{ route('kasir.pesanan.validasi', $pesanan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Batalkan validasi pembayaran pesanan #{{ $pesanan->id }}?')">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status_pembayaran" value="Belum Dibayar">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Batalkan validasi">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <form action="{{ route('kasir.pesanan.validasi', $pesanan->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status_pembayaran" value="Sudah Dibayar">
                                    <button type="submit" class="btn btn-sm btn-success fw-bold text-nowrap shadow-sm">
                                        <i class="bi bi-check2-circle me-1"></i> Validasi Bayar
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="pe-3">
                            <form action="{{ route('kasir.pesanan.status', $pesanan->id) }}" method="POST" class="d-flex gap-1">
                                @csrf
                                @method('PUT')
                                <select name="status" class="form-select form-select-sm" style="min-width: 140px;">
                                    @foreach(['Menunggu Konfirmasi', 'Sedang Diproses', 'Siap Diambil', 'Selesai', 'Dibatalkan'] as $status)
                                        <option value="{{ $status }}" @selected($pesanan->status === $status)>{{ $status }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm" title="Simpan Status Pesanan">
                                    <i class="bi bi-save"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="bi bi-inbox display-5 d-block mb-2 text-secondary opacity-50"></i>
                            Belum ada data pesanan yang sesuai filter.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tampilkan QRIS Kasir -->
<div class="modal fade" id="modalQrisKasir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-qr-code me-2"></i>QRIS Kasir Kantin Sekolah
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="qris-box text-center mb-3">
                    <div class="bg-danger text-white py-1 px-3 rounded-2 fw-bold d-flex justify-content-between align-items-center mb-2">
                        <span><i class="bi bi-qr-code"></i> QRIS</span>
                        <span style="font-size: 0.7rem; letter-spacing: 1px;">STANDAR NASIONAL</span>
                    </div>
                    <div class="fw-bold text-dark fs-5">BI-KANTIN RESMI</div>
                    <div class="text-muted small mb-2">NMID: ID102030405060</div>

                    <!-- QR Mockup -->
                    <div class="d-flex justify-content-center my-3">
                        <svg width="220" height="220" viewBox="0 0 180 180" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="180" height="180" rx="8" fill="white"/>
                            <rect x="15" y="15" width="45" height="45" fill="black"/>
                            <rect x="22" y="22" width="31" height="31" fill="white"/>
                            <rect x="28" y="28" width="19" height="19" fill="black"/>
                            <rect x="120" y="15" width="45" height="45" fill="black"/>
                            <rect x="127" y="22" width="31" height="31" fill="white"/>
                            <rect x="133" y="28" width="19" height="19" fill="black"/>
                            <rect x="15" y="120" width="45" height="45" fill="black"/>
                            <rect x="22" y="127" width="31" height="31" fill="white"/>
                            <rect x="28" y="133" width="19" height="19" fill="black"/>
                            <rect x="70" y="18" width="8" height="8" fill="black"/>
                            <rect x="85" y="18" width="8" height="8" fill="black"/>
                            <rect x="100" y="18" width="8" height="8" fill="black"/>
                            <rect x="70" y="32" width="8" height="8" fill="black"/>
                            <rect x="92" y="32" width="8" height="8" fill="black"/>
                            <rect x="75" y="46" width="8" height="8" fill="black"/>
                            <rect x="85" y="52" width="8" height="8" fill="black"/>
                            <rect x="18" y="70" width="8" height="8" fill="black"/>
                            <rect x="32" y="70" width="8" height="8" fill="black"/>
                            <rect x="46" y="75" width="8" height="8" fill="black"/>
                            <rect x="18" y="85" width="8" height="8" fill="black"/>
                            <rect x="32" y="98" width="8" height="8" fill="black"/>
                            <rect x="125" y="70" width="8" height="8" fill="black"/>
                            <rect x="145" y="75" width="8" height="8" fill="black"/>
                            <rect x="132" y="88" width="8" height="8" fill="black"/>
                            <rect x="150" y="95" width="8" height="8" fill="black"/>
                            <rect x="70" y="125" width="8" height="8" fill="black"/>
                            <rect x="90" y="132" width="8" height="8" fill="black"/>
                            <rect x="75" y="145" width="8" height="8" fill="black"/>
                            <rect x="100" y="150" width="8" height="8" fill="black"/>
                            <rect x="125" y="125" width="8" height="8" fill="black"/>
                            <rect x="140" y="132" width="8" height="8" fill="black"/>
                            <rect x="128" y="145" width="8" height="8" fill="black"/>
                            <rect x="150" y="150" width="8" height="8" fill="black"/>
                            <rect x="68" y="68" width="44" height="44" rx="6" fill="#dc3545"/>
                            <text x="90" y="87" fill="white" font-size="9" font-weight="bold" font-family="sans-serif" text-anchor="middle">BI</text>
                            <text x="90" y="99" fill="white" font-size="8" font-weight="bold" font-family="sans-serif" text-anchor="middle">KANTIN</text>
                        </svg>
                    </div>

                    <div class="badge bg-danger text-white px-3 py-1">Tunjukkan barcode ini kepada siswa yang ingin membayar QRIS</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
