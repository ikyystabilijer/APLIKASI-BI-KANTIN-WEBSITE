<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog & Pesanan Siswa - Kantin Sekolah</title>
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
            transition: transform 0.15s ease-in-out;
        }
        .menu-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        .live-indicator {
            display: inline-block;
            width: 9px;
            height: 9px;
            background-color: #22c55e;
            border-radius: 50%;
            margin-right: 5px;
            box-shadow: 0 0 0 rgba(34, 197, 94, 0.4);
            animation: pulse 1.8s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('siswa.dashboard') }}">
            <i class="bi bi-shop me-1"></i> E-Kantin <span class="badge bg-light text-primary fs-6">Siswa</span>
        </a>
        <div class="d-flex align-items-center text-white">
            <span class="me-3 small">
                <i class="bi bi-person-fill me-1"></i> Halo, <strong>{{ session('username', 'Siswa') }}</strong>
            </span>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">
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

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Section 1: Katalog Menu Makanan & Minuman -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-grid-fill text-primary me-2"></i>Katalog Menu Kantin</h4>
                <p class="text-muted small mb-0">Pilih makanan dan minuman favoritmu, lalu klik Pesan.</p>
            </div>
            <span class="badge bg-primary px-3 py-2 fs-6">{{ count($menus) }} Menu Tersedia</span>
        </div>

        <div class="row g-3">
            @forelse($menus as $menu)
                <div class="col-md-6 col-lg-4">
                    <div class="card menu-card h-100 p-3 bg-white">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-light text-secondary border">
                                @if(stripos($menu->kategori, 'minum') !== false)
                                    <i class="bi bi-cup-straw text-primary me-1"></i>
                                @else
                                    <i class="bi bi-egg-fried text-warning me-1"></i>
                                @endif
                                {{ $menu->kategori }}
                            </span>
                            <span class="fw-bold text-success fs-5">
                                Rp {{ number_format($menu->harga, 0, ',', '.') }}
                            </span>
                        </div>

                        <h5 class="fw-bold mb-3 text-dark">{{ $menu->nama_menu }}</h5>

                        <div class="mt-auto pt-2 border-top">
                            <form action="{{ route('siswa.pesan') }}" method="POST">
                                @csrf
                                <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                                <div class="row g-2 align-items-center">
                                    <div class="col-5">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Qty</span>
                                            <input type="number" name="jumlah" class="form-control text-center" value="1" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-7">
                                        <button type="submit" class="btn btn-primary btn-sm w-100">
                                            <i class="bi bi-cart-plus me-1"></i> Pesan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card p-5 text-center text-muted">
                        <i class="bi bi-cup-hot display-4 mb-2"></i>
                        <h5>Belum ada menu yang tersedia saat ini.</h5>
                        <p class="mb-0">Silakan kembali lagi nanti saat kantin sudah buka.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Section 2: Status Pesanan Real-time -->
    <div class="card mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="card-title mb-0 fw-bold text-dark">
                    <i class="bi bi-clock-history text-primary me-2"></i>Status Pesanan Saya
                </h5>
                <small class="text-muted">
                    <span class="live-indicator"></span> Real-time monitoring aktif (otomatis refresh status)
                </small>
            </div>
            <button id="btnManualRefresh" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 70px;">ID</th>
                            <th>Menu</th>
                            <th>Jumlah</th>
                            <th>Total Harga</th>
                            <th class="pe-3">Status Pesanan</th>
                        </tr>
                    </thead>
                    <tbody id="pesananTableBody">
                        @forelse($pesanans as $p)
                            <tr id="pesanan-row-{{ $p->id }}">
                                <td class="ps-3 fw-bold">#{{ $p->id }}</td>
                                <td class="fw-semibold">{{ $p->menu->nama_menu ?? 'Menu Dihapus' }}</td>
                                <td>{{ $p->jumlah }} porsi</td>
                                <td class="text-success fw-bold">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</td>
                                <td class="pe-3">
                                    @php
                                        $badgeClass = match($p->status) {
                                            'Menunggu Konfirmasi' => 'bg-warning text-dark',
                                            'Sedang Diproses'     => 'bg-info text-dark',
                                            'Siap Diambil'        => 'bg-primary',
                                            'Selesai'             => 'bg-success',
                                            'Dibatalkan'          => 'bg-danger',
                                            default               => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} px-2 py-1 fs-6">
                                        @if($p->status === 'Siap Diambil')
                                            <i class="bi bi-bag-check-fill me-1"></i>
                                        @elseif($p->status === 'Sedang Diproses')
                                            <i class="bi bi-fire me-1"></i>
                                        @elseif($p->status === 'Menunggu Konfirmasi')
                                            <i class="bi bi-hourglass-split me-1"></i>
                                        @endif
                                        {{ $p->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRow">
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-receipt display-6 d-block mb-2"></i>
                                    Kamu belum memiliki riwayat pesanan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Real-time Status Polling Script -->
<script>
    function getStatusBadge(status) {
        let badgeClass = 'bg-secondary';
        let icon = '';

        if (status === 'Menunggu Konfirmasi') {
            badgeClass = 'bg-warning text-dark';
            icon = '<i class="bi bi-hourglass-split me-1"></i>';
        } else if (status === 'Sedang Diproses') {
            badgeClass = 'bg-info text-dark';
            icon = '<i class="bi bi-fire me-1"></i>';
        } else if (status === 'Siap Diambil') {
            badgeClass = 'bg-primary';
            icon = '<i class="bi bi-bag-check-fill me-1"></i>';
        } else if (status === 'Selesai') {
            badgeClass = 'bg-success';
            icon = '<i class="bi bi-check-circle-fill me-1"></i>';
        } else if (status === 'Dibatalkan') {
            badgeClass = 'bg-danger';
            icon = '<i class="bi bi-x-circle-fill me-1"></i>';
        }

        return `<span class="badge ${badgeClass} px-2 py-1 fs-6">${icon}${status}</span>`;
    }

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    async function fetchPesananRealtime() {
        try {
            const response = await fetch("{{ route('siswa.pesanan.status') }}");
            if (!response.ok) return;

            const pesanans = await response.json();
            const tableBody = document.getElementById('pesananTableBody');

            if (pesanans.length === 0) {
                tableBody.innerHTML = `
                    <tr id="emptyRow">
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="bi bi-receipt display-6 d-block mb-2"></i>
                            Kamu belum memiliki riwayat pesanan.
                        </td>
                    </tr>
                `;
                return;
            }

            let rowsHtml = '';
            pesanans.forEach(p => {
                const namaMenu = p.menu ? p.menu.nama_menu : 'Menu Dihapus';
                rowsHtml += `
                    <tr id="pesanan-row-${p.id}">
                        <td class="ps-3 fw-bold">#${p.id}</td>
                        <td class="fw-semibold">${namaMenu}</td>
                        <td>${p.jumlah} porsi</td>
                        <td class="text-success fw-bold">Rp ${formatRupiah(p.total_harga)}</td>
                        <td class="pe-3">${getStatusBadge(p.status)}</td>
                    </tr>
                `;
            });

            tableBody.innerHTML = rowsHtml;
        } catch (error) {
            console.error('Gagal mengambil status pesanan real-time:', error);
        }
    }

    // Polling setiap 5 detik untuk status real-time
    setInterval(fetchPesananRealtime, 5000);

    // Tombol refresh manual
    document.getElementById('btnManualRefresh')?.addEventListener('click', function() {
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memperbarui...';
        fetchPesananRealtime().then(() => {
            setTimeout(() => {
                this.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Refresh';
            }, 500);
        });
    });
</script>
</body>
</html>
