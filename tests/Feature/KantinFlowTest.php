<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KantinFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');

        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('BI-Kantin');
    }

    public function test_admin_login_and_dashboard_access(): void
    {
        $response = $this->post('/login', [
            'username' => 'admin_kantin',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/admin');

        $adminResponse = $this->withSession(['role' => 'admin', 'username' => 'admin_kantin'])
            ->get('/admin');
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Pengelolaan Menu Kantin');
        $adminResponse->assertSee('PESANAN HARI INI');
        $adminResponse->assertSee('Monitoring Pesanan Masuk');
    }

    public function test_admin_crud_menu_with_image_upload_and_update_pesanan(): void
    {
        Storage::fake('public');
        $fakeImage = UploadedFile::fake()->image('mie_ayam.jpg');

        // 1. Tambah menu beserta validasi upload gambar (Catatan No. 9)
        $createResponse = $this->withSession(['role' => 'admin', 'username' => 'admin_kantin'])
            ->post('/admin/menu', [
                'nama_menu' => 'Mie Ayam Bakso',
                'harga' => 15000,
                'kategori' => 'Makanan',
                'gambar' => $fakeImage,
            ]);
        $createResponse->assertRedirect('/admin');

        $menu = Menu::where('nama_menu', 'Mie Ayam Bakso')->first();
        $this->assertNotNull($menu);
        $this->assertEquals(15000, $menu->harga);
        $this->assertNotNull($menu->gambar);
        Storage::disk('public')->assertExists($menu->gambar);

        // 2. Update menu
        $updateResponse = $this->withSession(['role' => 'admin', 'username' => 'admin_kantin'])
            ->put('/admin/menu/'.$menu->id, [
                'nama_menu' => 'Mie Ayam Spesial',
                'harga' => 17000,
                'kategori' => 'Makanan',
            ]);
        $updateResponse->assertRedirect('/admin');
        $menu->refresh();
        $this->assertEquals('Mie Ayam Spesial', $menu->nama_menu);
        $this->assertEquals(17000, $menu->harga);

        // 3. Buat pesanan contoh & update status
        $siswa = User::where('role', 'siswa')->first();
        $pesanan = Pesanan::create([
            'user_id' => $siswa->id,
            'menu_id' => $menu->id,
            'jumlah' => 2,
            'total_harga' => 34000,
            'status' => 'Menunggu Konfirmasi',
        ]);

        $statusResponse = $this->withSession(['role' => 'admin', 'username' => 'admin_kantin'])
            ->put('/admin/pesanan/'.$pesanan->id.'/status', [
                'status' => 'Siap Diambil',
            ]);
        $statusResponse->assertRedirect('/admin');
        $pesanan->refresh();
        $this->assertEquals('Siap Diambil', $pesanan->status);

        // 4. Hapus menu & file gambar
        $deleteResponse = $this->withSession(['role' => 'admin', 'username' => 'admin_kantin'])
            ->delete('/admin/menu/'.$menu->id);
        $deleteResponse->assertRedirect('/admin');

        $this->assertNull(Menu::find($menu->id));
        Storage::disk('public')->assertMissing($menu->gambar);
    }

    public function test_siswa_cart_session_and_checkout_with_db_transaction(): void
    {
        $siswa = User::where('role', 'siswa')->first();
        $menu = Menu::first();

        // 1. Siswa menambah menu ke keranjang belanja (Catatan No. 10)
        $cartAddResponse = $this->withSession(['user_id' => $siswa->id, 'role' => 'siswa', 'username' => 'budi_siswa'])
            ->post('/siswa/keranjang', [
                'menu_id' => $menu->id,
                'jumlah' => 2,
            ]);
        $cartAddResponse->assertRedirect('/siswa');
        $cartAddResponse->assertSessionHas('cart');

        // 2. Siswa checkout keranjang belanja dengan DB Transaction (Catatan No. 11)
        $cartData = [
            $menu->id => [
                'menu_id' => $menu->id,
                'nama_menu' => $menu->nama_menu,
                'harga' => $menu->harga,
                'jumlah' => 2,
                'subtotal' => $menu->harga * 2,
                'gambar' => null,
                'kategori' => $menu->kategori,
            ],
        ];

        $checkoutResponse = $this->withSession([
            'user_id' => $siswa->id,
            'role' => 'siswa',
            'username' => 'budi_siswa',
            'cart' => $cartData,
        ])->post('/siswa/checkout');

        $checkoutResponse->assertRedirect('/siswa');
        $checkoutResponse->assertSessionMissing('cart');

        $pesanan = Pesanan::where('user_id', $siswa->id)->latest('id')->first();
        $this->assertNotNull($pesanan);
        $this->assertEquals($menu->harga * 2, $pesanan->total_harga);

        // 3. Siswa cek status pesanan realtime
        $realtimeResponse = $this->withSession(['user_id' => $siswa->id, 'role' => 'siswa', 'username' => 'budi_siswa'])
            ->getJson('/siswa/status-pesanan');
        $realtimeResponse->assertStatus(200);
        $realtimeResponse->assertJsonFragment([
            'id' => $pesanan->id,
            'metode_pembayaran' => 'Cash',
            'status_pembayaran' => 'Belum Dibayar',
        ]);
    }

    public function test_siswa_can_checkout_with_cash_and_qris_options(): void
    {
        $siswa = User::where('role', 'siswa')->first();
        $menu = Menu::first();

        // 1. Checkout dengan QRIS
        $cartQris = [
            $menu->id => [
                'menu_id' => $menu->id,
                'nama_menu' => $menu->nama_menu,
                'harga' => $menu->harga,
                'jumlah' => 1,
                'subtotal' => $menu->harga,
                'gambar' => null,
                'kategori' => $menu->kategori,
            ],
        ];

        $checkoutQrisResponse = $this->withSession([
            'user_id' => $siswa->id,
            'role' => 'siswa',
            'username' => 'budi_siswa',
            'cart' => $cartQris,
        ])->post('/siswa/checkout', [
            'metode_pembayaran' => 'QRIS',
        ]);

        $checkoutQrisResponse->assertRedirect('/siswa');
        $pesananQris = Pesanan::where('user_id', $siswa->id)->latest('id')->first();
        $this->assertEquals('QRIS', $pesananQris->metode_pembayaran);
        $this->assertEquals('Belum Dibayar', $pesananQris->status_pembayaran);

        // 2. Quick Order dengan Cash
        $pesanCashResponse = $this->withSession([
            'user_id' => $siswa->id,
            'role' => 'siswa',
            'username' => 'budi_siswa',
        ])->post('/siswa/pesan', [
            'menu_id' => $menu->id,
            'jumlah' => 3,
            'metode_pembayaran' => 'Cash',
        ]);

        $pesanCashResponse->assertRedirect('/siswa');
        $pesananCash = Pesanan::where('user_id', $siswa->id)->latest('id')->first();
        $this->assertEquals('Cash', $pesananCash->metode_pembayaran);
        $this->assertEquals('Belum Dibayar', $pesananCash->status_pembayaran);
        $this->assertEquals($menu->harga * 3, $pesananCash->total_harga);
    }

    public function test_kasir_can_view_orders_and_validate_payment(): void
    {
        $siswa = User::where('role', 'siswa')->first();
        $menu = Menu::first();

        $pesanan = Pesanan::create([
            'user_id' => $siswa->id,
            'menu_id' => $menu->id,
            'jumlah' => 2,
            'total_harga' => $menu->harga * 2,
            'metode_pembayaran' => 'QRIS',
            'status_pembayaran' => 'Belum Dibayar',
            'status' => 'Menunggu Konfirmasi',
        ]);

        // Kasir melihat pesanan di dashboard
        $kasirResponse = $this->withSession(['role' => 'kasir', 'username' => 'Kasir_kantin'])
            ->get('/kasir');
        $kasirResponse->assertStatus(200);
        $kasirResponse->assertSee('Validasi Pembayaran');
        $kasirResponse->assertSee('QRIS');
        $kasirResponse->assertSee('Belum Dibayar');

        // Kasir memvalidasi pembayaran
        $validasiResponse = $this->withSession(['role' => 'kasir', 'username' => 'Kasir_kantin'])
            ->put('/kasir/pesanan/'.$pesanan->id.'/validasi', [
                'status_pembayaran' => 'Sudah Dibayar',
            ]);
        $validasiResponse->assertRedirect();

        $pesanan->refresh();
        $this->assertEquals('Sudah Dibayar', $pesanan->status_pembayaran);
        // Otomatis lanjut ke Sedang Diproses
        $this->assertEquals('Sedang Diproses', $pesanan->status);

        // Kasir membatalkan validasi (toggle kembali ke Belum Dibayar)
        $batalValidasi = $this->withSession(['role' => 'kasir', 'username' => 'Kasir_kantin'])
            ->put('/kasir/pesanan/'.$pesanan->id.'/validasi', [
                'status_pembayaran' => 'Belum Dibayar',
            ]);
        $batalValidasi->assertRedirect();
        $pesanan->refresh();
        $this->assertEquals('Belum Dibayar', $pesanan->status_pembayaran);
    }

    public function test_kasir_can_filter_by_metode_and_status_pembayaran(): void
    {
        $siswa = User::where('role', 'siswa')->first();
        $menu = Menu::first();

        $p1 = Pesanan::create([
            'user_id' => $siswa->id,
            'menu_id' => $menu->id,
            'jumlah' => 1,
            'total_harga' => $menu->harga,
            'metode_pembayaran' => 'QRIS',
            'status_pembayaran' => 'Belum Dibayar',
            'status' => 'Menunggu Konfirmasi',
        ]);

        $p2 = Pesanan::create([
            'user_id' => $siswa->id,
            'menu_id' => $menu->id,
            'jumlah' => 1,
            'total_harga' => $menu->harga,
            'metode_pembayaran' => 'Cash',
            'status_pembayaran' => 'Sudah Dibayar',
            'status' => 'Sedang Diproses',
        ]);

        // Filter QRIS
        $responseQris = $this->withSession(['role' => 'kasir', 'username' => 'Kasir_kantin'])
            ->get('/kasir?metode_pembayaran=QRIS');
        $responseQris->assertStatus(200);
        $responseQris->assertSee('#'.$p1->id);

        // Filter Status Pembayaran Sudah Dibayar
        $responseSudah = $this->withSession(['role' => 'kasir', 'username' => 'Kasir_kantin'])
            ->get('/kasir?status_pembayaran=Sudah+Dibayar');
        $responseSudah->assertStatus(200);
        $responseSudah->assertSee('#'.$p2->id);
    }

    public function test_role_protection(): void
    {
        // Siswa tidak boleh akses /admin atau /kasir
        $response = $this->withSession(['role' => 'siswa'])->get('/admin');
        $response->assertStatus(403);
        $responseKasir = $this->withSession(['role' => 'siswa'])->get('/kasir');
        $responseKasir->assertStatus(403);

        // Kasir tidak boleh akses /admin atau /siswa
        $responseAdmin = $this->withSession(['role' => 'kasir'])->get('/admin');
        $responseAdmin->assertStatus(403);
        $responseSiswa = $this->withSession(['role' => 'kasir'])->get('/siswa');
        $responseSiswa->assertStatus(403);

        // Admin tidak boleh akses /siswa
        $response = $this->withSession(['role' => 'admin'])->get('/siswa');
        $response->assertStatus(403);
    }
}
