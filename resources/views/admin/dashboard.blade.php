<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kantin Sekolah</title>
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
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-shield-lock-fill me-1 text-primary"></i> E-Kantin <span class="badge bg-primary fs-6">Admin</span>
        </a>
        <div class="d-flex align-items-center text-white">
            <span class="me-3 small d-none d-md-inline">
                <i class="bi bi-person-circle me-1"></i> {{ session('username', 'Admin') }}
            </span>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </button>
            </form>
        </div>
    </div>
</nav>

<div class="container py-4">
    <!-- Notifications -->
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

    <!-- Statistic Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card p-3 bg-white border-start border-primary border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">TOTAL MENU</div>
                        <h3 class="fw-bold mb-0 text-primary">{{ $totalMenu }}</h3>
                    </div>
                    <i class="bi bi-egg-fried display-6 text-primary opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 bg-white border-start border-warning border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">MENUNGGU KONFIRMASI</div>
                        <h3 class="fw-bold mb-0 text-warning">{{ $pesananMenunggu }}</h3>
                    </div>
                    <i class="bi bi-clock-history display-6 text-warning opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 bg-white border-start border-success border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">TOTAL PESANAN</div>
                        <h3 class="fw-bold mb-0 text-success">{{ $totalPesanan }}</h3>
                    </div>
                    <i class="bi bi-receipt display-6 text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Monitoring Pesanan Masuk -->
    <div class="card mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold text-dark">
                <i class="bi bi-bell-fill text-warning me-2"></i>Monitoring Pesanan Masuk
            </h5>
            <span class="badge bg-secondary">{{ count($pesanans) }} Pesanan</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">ID</th>
                            <th>Nama Siswa</th>
                            <th>Menu</th>
                            <th>Jumlah</th>
                            <th>Total Harga</th>
                            <th>Status Saat Ini</th>
                            <th class="text-end pe-3" style="min-width: 250px;">Ubah Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pesanans as $p)
                            <tr>
                                <td class="ps-3 fw-bold">#{{ $p->id }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-person me-1"></i>{{ $p->user->username ?? 'User #' . $p->user_id }}
                                    </span>
                                </td>
                                <td class="fw-semibold">{{ $p->menu->nama_menu ?? 'Menu Dihapus' }}</td>
                                <td>{{ $p->jumlah }} porsi</td>
                                <td class="text-success fw-bold">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</td>
                                <td>
                                    @php
                                        $badgeColor = match($p->status) {
                                            'Menunggu Konfirmasi' => 'bg-warning text-dark',
                                            'Sedang Diproses'     => 'bg-info text-dark',
                                            'Siap Diambil'        => 'bg-primary',
                                            'Selesai'             => 'bg-success',
                                            'Dibatalkan'          => 'bg-danger',
                                            default               => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeColor }}">{{ $p->status }}</span>
                                </td>
                                <td class="text-end pe-3">
                                    <form action="{{ route('admin.pesanan.status', $p->id) }}" method="POST" class="d-inline-flex gap-1">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" class="form-select form-select-sm" style="width: auto;">
                                            <option value="Menunggu Konfirmasi" {{ $p->status === 'Menunggu Konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                                            <option value="Sedang Diproses" {{ $p->status === 'Sedang Diproses' ? 'selected' : '' }}>Sedang Diproses</option>
                                            <option value="Siap Diambil" {{ $p->status === 'Siap Diambil' ? 'selected' : '' }}>Siap Diambil</option>
                                            <option value="Selesai" {{ $p->status === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                            <option value="Dibatalkan" {{ $p->status === 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Simpan Status">
                                            <i class="bi bi-check-lg"></i> Update
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                    Belum ada pesanan masuk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section: CRUD Menu Makanan & Minuman -->
    <div class="card">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold text-dark">
                <i class="bi bi-card-list text-primary me-2"></i>Pengelolaan Menu Kantin
            </h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahMenu">
                <i class="bi bi-plus-circle me-1"></i> Tambah Menu Baru
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3" style="width: 50px;">No</th>
                            <th>Nama Menu</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th class="text-end pe-3" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($menus as $index => $m)
                            <tr>
                                <td class="ps-3">{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $m->nama_menu }}</td>
                                <td>
                                    <span class="badge bg-light text-secondary border">{{ $m->kategori }}</span>
                                </td>
                                <td>Rp {{ number_format($m->harga, 0, ',', '.') }}</td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-warning btn-sm text-dark me-1" data-bs-toggle="modal" data-bs-target="#modalEditMenu{{ $m->id }}" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form action="{{ route('admin.menu.destroy', $m->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus menu ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal Edit Menu -->
                            <div class="modal fade" id="modalEditMenu{{ $m->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.menu.update', $m->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Edit Menu</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Nama Menu</label>
                                                    <input type="text" name="nama_menu" class="form-control" value="{{ $m->nama_menu }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Kategori</label>
                                                    <select name="kategori" class="form-select" required>
                                                        <option value="Makanan" {{ $m->kategori === 'Makanan' ? 'selected' : '' }}>Makanan</option>
                                                        <option value="Minuman" {{ $m->kategori === 'Minuman' ? 'selected' : '' }}>Minuman</option>
                                                        <option value="Camilan" {{ $m->kategori === 'Camilan' ? 'selected' : '' }}>Camilan</option>
                                                        <option value="Lainnya" {{ !in_array($m->kategori, ['Makanan', 'Minuman', 'Camilan']) ? 'selected' : '' }}>Lainnya</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Harga (Rp)</label>
                                                    <input type="number" name="harga" class="form-control" value="{{ $m->harga }}" min="0" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada data menu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Menu -->
<div class="modal fade" id="modalTambahMenu" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.menu.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Menu Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Menu</label>
                        <input type="text" name="nama_menu" class="form-control" placeholder="Contoh: Nasi Uduk Komplit" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kategori</label>
                        <select name="kategori" class="form-select" required>
                            <option value="Makanan">Makanan</option>
                            <option value="Minuman">Minuman</option>
                            <option value="Camilan">Camilan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Harga (Rp)</label>
                        <input type="number" name="harga" class="form-control" placeholder="Contoh: 12000" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Menu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
