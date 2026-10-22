<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $response->assertSee('E-Kantin');
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
        $adminResponse->assertSee('Monitoring Pesanan Masuk');
    }

    public function test_admin_crud_menu_and_update_pesanan(): void
    {
        // 1. Tambah menu
        $createResponse = $this->withSession(['role' => 'admin', 'username' => 'admin_kantin'])
            ->post('/admin/menu', [
                'nama_menu' => 'Bakso Sapi Urat',
                'harga' => 12000,
                'kategori' => 'Makanan',
            ]);
        $createResponse->assertRedirect('/admin');

        $menu = Menu::where('nama_menu', 'Bakso Sapi Urat')->first();
        $this->assertNotNull($menu);
        $this->assertEquals(12000, $menu->harga);

        // 2. Update menu
        $updateResponse = $this->withSession(['role' => 'admin', 'username' => 'admin_kantin'])
            ->put('/admin/menu/' . $menu->id, [
                'nama_menu' => 'Bakso Sapi Super',
                'harga' => 14000,
                'kategori' => 'Makanan',
            ]);
        $updateResponse->assertRedirect('/admin');
        $menu->refresh();
        $this->assertEquals('Bakso Sapi Super', $menu->nama_menu);
        $this->assertEquals(14000, $menu->harga);

        // 3. Buat pesanan contoh & update status
        $siswa = User::where('role', 'siswa')->first();
        $pesanan = Pesanan::create([
            'user_id' => $siswa->id,
            'menu_id' => $menu->id,
            'jumlah' => 2,
            'total_harga' => 28000,
            'status' => 'Menunggu Konfirmasi',
        ]);

        $statusResponse = $this->withSession(['role' => 'admin', 'username' => 'admin_kantin'])
            ->put('/admin/pesanan/' . $pesanan->id . '/status', [
                'status' => 'Siap Diambil',
            ]);
        $statusResponse->assertRedirect('/admin');
        $pesanan->refresh();
        $this->assertEquals('Siap Diambil', $pesanan->status);

        // 4. Hapus menu
        $deleteResponse = $this->withSession(['role' => 'admin', 'username' => 'admin_kantin'])
            ->delete('/admin/menu/' . $menu->id);
        $deleteResponse->assertRedirect('/admin');

        $this->assertNull(Menu::find($menu->id));
    }

    public function test_siswa_flow(): void
    {
        $siswa = User::where('role', 'siswa')->first();

        // 1. Login siswa
        $loginResponse = $this->post('/login', [
            'username' => 'budi_siswa',
            'password' => '123',
        ]);
        $loginResponse->assertRedirect('/siswa');

        // 2. Akses dashboard siswa
        $dashboardResponse = $this->withSession(['user_id' => $siswa->id, 'role' => 'siswa', 'username' => 'budi_siswa'])
            ->get('/siswa');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Katalog Menu Kantin');
        $dashboardResponse->assertSee('Status Pesanan Saya');

        // 3. Siswa memesan menu
        $menu = Menu::first();
        $pesanResponse = $this->withSession(['user_id' => $siswa->id, 'role' => 'siswa', 'username' => 'budi_siswa'])
            ->post('/siswa/pesan', [
                'menu_id' => $menu->id,
                'jumlah' => 2,
            ]);
        $pesanResponse->assertRedirect('/siswa');

        $pesanan = Pesanan::where('user_id', $siswa->id)->latest('id')->first();
        $this->assertNotNull($pesanan);
        $this->assertEquals($menu->harga * 2, $pesanan->total_harga);

        // 4. Siswa get status pesanan realtime (JSON)
        $realtimeResponse = $this->withSession(['user_id' => $siswa->id, 'role' => 'siswa', 'username' => 'budi_siswa'])
            ->getJson('/siswa/status-pesanan');
        $realtimeResponse->assertStatus(200);
        $realtimeResponse->assertJsonFragment(['id' => $pesanan->id]);
    }

    public function test_role_protection(): void
    {
        // Siswa tidak boleh akses /admin
        $response = $this->withSession(['role' => 'siswa'])->get('/admin');
        $response->assertStatus(403);

        // Admin tidak boleh akses /siswa
        $response = $this->withSession(['role' => 'admin'])->get('/siswa');
        $response->assertStatus(403);
    }
}
