<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_admin_can_create_update_search_and_soft_delete_users(): void
    {
        $this->seed();

        $createResponse = $this->withSession(['role' => 'admin'])->post('/admin/users', [
            'username' => 'siti_siswa',
            'email' => 'siti@example.com',
            'password' => 'password123',
            'role' => 'siswa',
        ]);

        $createResponse->assertRedirect('/admin');
        $user = User::where('username', 'siti_siswa')->firstOrFail();
        $this->assertSame('password123', $user->password);

        $updateResponse = $this->withSession(['role' => 'admin'])->put('/admin/users/'.$user->id, [
            'username' => 'siti_aktif',
            'email' => 'siti@example.com',
            'password' => '',
            'role' => 'siswa',
            'status' => 1,
        ]);
        $updateResponse->assertRedirect('/admin');

        $this->withSession(['role' => 'admin'])
            ->get('/admin?search=siti_aktif&role=siswa')
            ->assertOk()
            ->assertSee('siti_aktif');

        $deleteResponse = $this->withSession(['role' => 'admin'])
            ->delete('/admin/users/'.$user->id);
        $deleteResponse->assertRedirect('/admin');
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_cashier_account_can_login(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'username' => 'Kasir_kantin',
            'password' => 'kasir123',
        ]);

        $response->assertRedirect('/kasir');
    }
}
