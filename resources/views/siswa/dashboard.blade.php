<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog & Pesanan Siswa - BI-Kantin</title>
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
        .menu-img-container {
            height: 140px;
            overflow: hidden;
            border-radius: 0.5rem 0.5rem 0 0;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .menu-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
            <i class="bi bi-shop me-1"></i> BI-Kantin <span class="badge bg-light text-primary fs-6">Siswa</span>
        </a>
        <div class="d-flex align-items-center gap-2">
            <!-- Tombol Keranjang Belanja (Catatan Guru No. 10) -->
            <button class="btn btn-warning btn-sm text-dark position-relative fw-bold" data-bs-toggle="modal" data-bs-target="#modalKeranjang">
                <i class="bi bi-cart3 me-1"></i> Keranjang
                @php $cartCount = count($cart ?? []); @endphp
                @if($cartCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $cartCount }}
                    </span>
                @endif
            </button>

            <span class="text-white small ms-2 d-none d-md-inline">
                <i class="bi bi-person-fill me-1"></i> {{ session('username', 'Siswa') }}
            </span>

            <form action="{{ route('logout') }}" method="POST" class="d-inline ms-1">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i>
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
                <p class="text-muted small mb-0">Pilih menu favoritmu, tambahkan ke keranjang atau langsung pesan.</p>
            </div>
            <span class="badge bg-primary px-3 py-2 fs-6">{{ count($menus) }} Menu</span>
        </div>

        <div class="row g-3">
            @forelse($menus as $menu)
                <div class="col-md-6 col-lg-4">
                    <div class="card menu-card h-100 bg-white overflow-hidden">
                        <!-- Gambar Menu -->
                        <div class="menu-img-container">
                            @if($menu->gambar)
                                <img src="{{ asset('storage/' . $menu->gambar) }}" alt="{{ $menu->nama_menu }}" class="menu-img">
                            @else
                                <div class="text-center text-muted">
                                    @if(stripos($menu->kategori, 'minum') !== false)
                                        <i class="bi bi-cup-straw display-4 text-primary opacity-50"></i>
                                    @else
                                        <i class="bi bi-egg-fried display-4 text-warning opacity-50"></i>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="p-3 d-flex flex-column flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-light text-secondary border">
                                    {{ $menu->kategori }}
                                </span>
                                <span class="fw-bold text-success fs-5">
                                    Rp {{ number_format($menu->harga, 0, ',', '.') }}
                                </span>
                            </div>

                            <h5 class="fw-bold mb-3 text-dark">{{ $menu->nama_menu }}</h5>

                            <div class="mt-auto pt-2 border-top">
                                <!-- Form Tambah ke Keranjang Session (Catatan No. 10) -->
                                <form action="{{ route('siswa.cart.add') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-4">
                                            <input type="number" name="jumlah" class="form-control form-control-sm text-center" value="1" min="1" required>
                                        </div>
                                        <div class="col-8">
                                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                                <i class="bi bi-cart-plus me-1"></i> + Keranjang
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card p-5 text-center text-muted">
                        <i class="bi bi-cup-hot display-4 mb-2"></i>
                        <h5>Belum ada menu yang tersedia saat ini.</h5>
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
                    <span class="live-indicator"></span> Real-time monitoring aktif (otomatis memperbarui status)
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
                            <th>Waktu</th>
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
                                <td class="text-muted small">
                                    {{ $p->created_at ? $p->created_at->format('d/m H:i') : '-' }}
                                </td>
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
                                        {{ $p->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRow">
                                <td colspan="6" class="text-center py-4 text-muted">
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

<!-- Modal Keranjang Belanja (Catatan Guru No. 10 & 11) -->
<div class="modal fade" id="modalKeranjang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-cart3 me-2"></i>Keranjang Belanja Siswa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                @if(count($cart ?? []) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Menu</th>
                                    <th>Harga</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cart as $id => $item)
                                    <tr>
                                        <td class="ps-3 fw-semibold">{{ $item['nama_menu'] }}</td>
                                        <td>Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>
                                        <td>{{ $item['jumlah'] }}x</td>
                                        <td class="fw-bold text-success">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                                        <td class="text-end pe-3">
                                            <form action="{{ route('siswa.cart.remove', $id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus Item">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="ps-3 text-end fs-6">Total Belanja:</th>
                                    <th colspan="2" class="text-success fs-5 fw-bold">Rp {{ number_format($totalKeranjang, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-cart-x display-3 mb-2 text-secondary opacity-50"></i>
                        <h5>Keranjang belanja masih kosong</h5>
                        <p class="small mb-0">Silakan pilih makanan atau minuman dari katalog untuk memesan.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div>
                    @if(count($cart ?? []) > 0)
                        <form action="{{ route('siswa.cart.clear') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm" onclick="return confirm('Kosongkan semua item di keranjang?')">
                                Kosongkan Keranjang
                            </button>
                        </form>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    @if(count($cart ?? []) > 0)
                        <!-- Checkout dengan DB::transaction (Catatan No. 11) -->
                        <form action="{{ route('siswa.cart.checkout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success fw-bold">
                                <i class="bi bi-check-circle me-1"></i> Checkout Sekarang (Rp {{ number_format($totalKeranjang, 0, ',', '.') }})
                            </button>
                        </form>
                    @endif
                </div>
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

        if (status === 'Menunggu Konfirmasi') {
            badgeClass = 'bg-warning text-dark';
        } else if (status === 'Sedang Diproses') {
            badgeClass = 'bg-info text-dark';
        } else if (status === 'Siap Diambil') {
            badgeClass = 'bg-primary';
        } else if (status === 'Selesai') {
            badgeClass = 'bg-success';
        } else if (status === 'Dibatalkan') {
            badgeClass = 'bg-danger';
        }

        return `<span class="badge ${badgeClass} px-2 py-1 fs-6">${status}</span>`;
    }

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    function formatWaktu(dateString) {
        if (!dateString) return '-';
        const d = new Date(dateString);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');
        return `${day}/${month} ${hours}:${minutes}`;
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
                        <td colspan="6" class="text-center py-4 text-muted">
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
                        <td class="text-muted small">${formatWaktu(p.created_at)}</td>
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

    // Polling setiap 5 detik
    setInterval(fetchPesananRealtime, 5000);

    // Tombol refresh manual
    document.getElementById('btnManualRefresh')?.addEventListener('click', function() {
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';
        fetchPesananRealtime().then(() => {
            setTimeout(() => {
                this.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Refresh';
            }, 400);
        });
    });
</script>
</body>
</html>
