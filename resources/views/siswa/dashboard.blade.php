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
            width: 100%;
            aspect-ratio: 1 / 1;
            overflow: hidden;
            border-radius: 0.75rem 0.75rem 0 0;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .menu-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .menu-card:hover .menu-img {
            transform: scale(1.04);
        }
        .menu-placeholder-icon {
            font-size: 4rem;
            opacity: 0.4;
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
        .payment-card {
            cursor: pointer;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            transition: all 0.2s ease;
        }
        .payment-card:hover {
            border-color: #93c5fd;
            background-color: #f8fafc;
        }
        .payment-card.selected {
            border-color: #0d6efd;
            background-color: #eff6ff;
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
                <p class="text-muted small mb-0">Pilih menu favoritmu, tambahkan ke keranjang, dan bayar lewat Cash atau QRIS.</p>
            </div>
            <span class="badge bg-primary px-3 py-2 fs-6">{{ count($menus) }} Menu</span>
        </div>

        <div class="row g-4">
            @forelse($menus as $menu)
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="card menu-card h-100 bg-white overflow-hidden shadow-sm">
                        <!-- Gambar Menu (Ukuran 1:1) -->
                        <div class="menu-img-container">
                            @if($menu->gambar)
                                <img src="{{ asset('storage/' . $menu->gambar) }}" alt="{{ $menu->nama_menu }}" class="menu-img">
                            @else
                                <div class="text-center text-muted">
                                    @if(stripos($menu->kategori, 'minum') !== false)
                                        <i class="bi bi-cup-straw menu-placeholder-icon text-primary"></i>
                                    @else
                                        <i class="bi bi-egg-fried menu-placeholder-icon text-warning"></i>
                                    @endif
                                </div>
                            @endif
                            <span class="position-absolute top-0 start-0 m-2 badge bg-dark bg-opacity-75">
                                {{ $menu->kategori }}
                            </span>
                        </div>

                        <div class="p-3 d-flex flex-column flex-grow-1">
                            <h6 class="fw-bold text-dark mb-1 text-truncate" title="{{ $menu->nama_menu }}">{{ $menu->nama_menu }}</h6>
                            <div class="fw-bold text-success fs-5 mb-3">
                                Rp {{ number_format($menu->harga, 0, ',', '.') }}
                            </div>

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
                    <span class="live-indicator"></span> Real-time monitoring aktif (otomatis memperbarui status pesanan & validasi pembayaran)
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
                            <th>Total</th>
                            <th>Metode Bayar</th>
                            <th>Status Pembayaran</th>
                            <th>Status Pesanan</th>
                            <th class="pe-3 text-end" style="width: 110px;">Aksi</th>
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
                                <td>
                                    @if(strtoupper($p->metode_pembayaran) === 'QRIS')
                                        <span class="badge bg-danger text-white"><i class="bi bi-qr-code me-1"></i> QRIS</span>
                                    @else
                                        <span class="badge bg-secondary text-white"><i class="bi bi-cash me-1"></i> Cash</span>
                                    @endif
                                </td>
                                <td>
                                    @if($p->status_pembayaran === 'Sudah Dibayar')
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Sudah Dibayar</span>
                                    @else
                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Belum Dibayar</span>
                                    @endif
                                </td>
                                <td>
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
                                <td class="pe-3 text-end">
                                    @if(strtoupper($p->metode_pembayaran) === 'QRIS')
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="showQrisModal('{{ $p->id }}', '{{ $p->total_harga }}', '{{ $p->menu->nama_menu ?? 'Pesanan' }}')">
                                            <i class="bi bi-qr-code-scan me-1"></i> QRIS
                                        </button>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRow">
                                <td colspan="9" class="text-center py-4 text-muted">
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

<!-- Modal Keranjang Belanja & Opsi Pembayaran (Catatan Guru No. 10 & 11) -->
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

                    <!-- Form Opsi Pembayaran (Cash / QRIS) -->
                    <form id="formCheckout" action="{{ route('siswa.cart.checkout') }}" method="POST">
                        @csrf
                        <div class="p-4 bg-light border-top">
                            <label class="form-label fw-bold mb-3 d-flex align-items-center">
                                <i class="bi bi-credit-card-2-front-fill text-primary me-2 fs-5"></i>
                                Pilih Metode Pembayaran
                            </label>

                            <div class="row g-3 mb-3">
                                <!-- Opsi Cash -->
                                <div class="col-sm-6">
                                    <div class="payment-card p-3 h-100 selected" id="cardOptionCash" onclick="selectPaymentMethod('Cash')">
                                        <div class="form-check d-flex align-items-start">
                                            <input class="form-check-input me-2 mt-1" type="radio" name="metode_pembayaran" id="radioPaymentCash" value="Cash" checked>
                                            <label class="form-check-label w-100" for="radioPaymentCash" style="cursor: pointer;">
                                                <div class="fw-bold text-dark fs-6 d-flex align-items-center">
                                                    <i class="bi bi-cash-stack text-success fs-4 me-2"></i> Cash (Tunai)
                                                </div>
                                                <small class="text-muted d-block mt-1">
                                                    Bayar langsung di kasir kantin saat mengambil makanan.
                                                </small>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Opsi QRIS -->
                                <div class="col-sm-6">
                                    <div class="payment-card p-3 h-100" id="cardOptionQris" onclick="selectPaymentMethod('QRIS')">
                                        <div class="form-check d-flex align-items-start">
                                            <input class="form-check-input me-2 mt-1" type="radio" name="metode_pembayaran" id="radioPaymentQris" value="QRIS">
                                            <label class="form-check-label w-100" for="radioPaymentQris" style="cursor: pointer;">
                                                <div class="fw-bold text-dark fs-6 d-flex align-items-center">
                                                    <i class="bi bi-qr-code-scan text-danger fs-4 me-2"></i> QRIS
                                                </div>
                                                <small class="text-muted d-block mt-1">
                                                    Scan barcode QRIS via GoPay, OVO, Dana, ShopeePay, atau Mobile Banking.
                                                </small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Petunjuk / Preview Box QRIS -->
                            <div id="qrisPreviewContainer" class="d-none text-center pt-2">
                                <div class="alert alert-info small text-start py-2 px-3 mb-3">
                                    <i class="bi bi-info-circle-fill me-1"></i>
                                    <strong>Petunjuk Pembayaran QRIS:</strong> Silakan scan QRIS di bawah ini melalui aplikasi e-wallet Anda sejumlah nominal belanja, lalu klik checkout dan tunjukkan bukti pembayaran ke kasir kantin.
                                </div>

                                <div class="qris-box text-center">
                                    <!-- Header QRIS -->
                                    <div class="bg-danger text-white py-1 px-3 rounded-2 fw-bold d-flex justify-content-between align-items-center mb-2">
                                        <span><i class="bi bi-qr-code"></i> QRIS</span>
                                        <span style="font-size: 0.7rem; letter-spacing: 1px;">STANDAR NASIONAL</span>
                                    </div>
                                    <div class="fw-bold text-dark fs-6">BI-KANTIN RESMI</div>
                                    <div class="text-muted small mb-2" style="font-size: 0.75rem;">NMID: ID102030405060</div>

                                    <!-- QRIS SVG Mockup -->
                                    <div class="d-flex justify-content-center my-2">
                                        <svg width="180" height="180" viewBox="0 0 180 180" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="180" height="180" rx="8" fill="white"/>
                                            <!-- Top-left position marker -->
                                            <rect x="15" y="15" width="45" height="45" fill="black"/>
                                            <rect x="22" y="22" width="31" height="31" fill="white"/>
                                            <rect x="28" y="28" width="19" height="19" fill="black"/>
                                            <!-- Top-right position marker -->
                                            <rect x="120" y="15" width="45" height="45" fill="black"/>
                                            <rect x="127" y="22" width="31" height="31" fill="white"/>
                                            <rect x="133" y="28" width="19" height="19" fill="black"/>
                                            <!-- Bottom-left position marker -->
                                            <rect x="15" y="120" width="45" height="45" fill="black"/>
                                            <rect x="22" y="127" width="31" height="31" fill="white"/>
                                            <rect x="28" y="133" width="19" height="19" fill="black"/>
                                            <!-- Simulated Data Pixels -->
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
                                            <!-- Center Logo Badge -->
                                            <rect x="68" y="68" width="44" height="44" rx="6" fill="#dc3545"/>
                                            <text x="90" y="87" fill="white" font-size="9" font-weight="bold" font-family="sans-serif" text-anchor="middle">BI</text>
                                            <text x="90" y="99" fill="white" font-size="8" font-weight="bold" font-family="sans-serif" text-anchor="middle">KANTIN</text>
                                        </svg>
                                    </div>

                                    <div class="badge bg-light text-danger border border-danger px-3 py-1 fs-6 mt-1">
                                        Total: <strong>Rp {{ number_format($totalKeranjang, 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
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
                        <button type="button" class="btn btn-success fw-bold" onclick="document.getElementById('formCheckout').submit()">
                            <i class="bi bi-check-circle me-1"></i> <span id="btnCheckoutText">Checkout & Bayar Cash (Rp {{ number_format($totalKeranjang, 0, ',', '.') }})</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tampilkan Kode QRIS Pesanan -->
<div class="modal fade" id="modalLihatQris" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-qr-code me-2"></i>QRIS Pembayaran Kantin
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
                        <svg width="210" height="210" viewBox="0 0 180 180" fill="none" xmlns="http://www.w3.org/2000/svg">
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

                    <div class="text-muted small">Pesanan: <span id="modalQrisMenuTitle" class="fw-bold text-dark">-</span> (#<span id="modalQrisOrderId">-</span>)</div>
                    <div class="fs-4 fw-bold text-danger mt-1" id="modalQrisAmount">Rp 0</div>
                </div>

                <div class="alert alert-secondary small text-start mb-0">
                    <i class="bi bi-shield-check text-success me-1"></i>
                    Setelah pembayaran berhasil melalui mobile banking / e-wallet, silakan tunjukkan bukti transaksi ini ke <strong>Kasir Kantin</strong> untuk divalidasi.
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

<script>
    // Pemilihan Metode Pembayaran di Checkout Modal
    function selectPaymentMethod(method) {
        const radioCash = document.getElementById('radioPaymentCash');
        const radioQris = document.getElementById('radioPaymentQris');
        const cardCash = document.getElementById('cardOptionCash');
        const cardQris = document.getElementById('cardOptionQris');
        const qrisPreview = document.getElementById('qrisPreviewContainer');
        const btnCheckoutText = document.getElementById('btnCheckoutText');
        const total = '{{ number_format($totalKeranjang ?? 0, 0, ',', '.') }}';

        if (method === 'QRIS') {
            radioQris.checked = true;
            cardQris.classList.add('selected');
            cardCash.classList.remove('selected');
            qrisPreview.classList.remove('d-none');
            if (btnCheckoutText) {
                btnCheckoutText.innerText = `Checkout & Bayar QRIS (Rp ${total})`;
            }
        } else {
            radioCash.checked = true;
            cardCash.classList.add('selected');
            cardQris.classList.remove('selected');
            qrisPreview.classList.add('d-none');
            if (btnCheckoutText) {
                btnCheckoutText.innerText = `Checkout & Bayar Cash (Rp ${total})`;
            }
        }
    }

    // Modal QRIS Pesanan
    function showQrisModal(orderId, totalHarga, menuName) {
        document.getElementById('modalQrisOrderId').innerText = orderId;
        document.getElementById('modalQrisAmount').innerText = 'Rp ' + formatRupiah(totalHarga);
        document.getElementById('modalQrisMenuTitle').innerText = menuName;
        const modal = new bootstrap.Modal(document.getElementById('modalLihatQris'));
        modal.show();
    }

    // Helper Status Pesanan
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

    // Helper Status Pembayaran
    function getPaymentStatusBadge(status) {
        if (status === 'Sudah Dibayar') {
            return `<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Sudah Dibayar</span>`;
        }
        return `<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Belum Dibayar</span>`;
    }

    // Helper Metode Pembayaran
    function getPaymentMethodBadge(method) {
        if (method && method.toUpperCase() === 'QRIS') {
            return `<span class="badge bg-danger text-white"><i class="bi bi-qr-code me-1"></i> QRIS</span>`;
        }
        return `<span class="badge bg-secondary text-white"><i class="bi bi-cash me-1"></i> Cash</span>`;
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
                        <td colspan="9" class="text-center py-4 text-muted">
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
                const isQris = (p.metode_pembayaran || '').toUpperCase() === 'QRIS';
                const aksiQris = isQris
                    ? `<button type="button" class="btn btn-outline-danger btn-sm" onclick="showQrisModal('${p.id}', '${p.total_harga}', '${namaMenu.replace(/'/g, "\\'")}')"><i class="bi bi-qr-code-scan me-1"></i> QRIS</button>`
                    : '<span class="text-muted small">-</span>';

                rowsHtml += `
                    <tr id="pesanan-row-${p.id}">
                        <td class="ps-3 fw-bold">#${p.id}</td>
                        <td class="text-muted small">${formatWaktu(p.created_at)}</td>
                        <td class="fw-semibold">${namaMenu}</td>
                        <td>${p.jumlah} porsi</td>
                        <td class="text-success fw-bold">Rp ${formatRupiah(p.total_harga)}</td>
                        <td>${getPaymentMethodBadge(p.metode_pembayaran)}</td>
                        <td>${getPaymentStatusBadge(p.status_pembayaran)}</td>
                        <td>${getStatusBadge(p.status)}</td>
                        <td class="pe-3 text-end">${aksiQris}</td>
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
