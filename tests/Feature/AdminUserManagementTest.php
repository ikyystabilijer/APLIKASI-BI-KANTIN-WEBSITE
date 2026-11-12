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

    public function test_admin_cannot_delete_own_account(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();

        $response = $this->actingAs($admin)
            ->withSession(['role' => 'admin', 'user_id' => $admin->id])
            ->delete('/admin/users/'.$admin->id);

        $response->assertSessionHas('error', 'Akun admin yang sedang digunakan tidak dapat dihapus.');
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'deleted_at' => null,
        ]);
    }

    public function test_dashboard_does_not_place_modals_inside_tbody(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();

        $response = $this->actingAs($admin)
            ->withSession(['role' => 'admin', 'user_id' => $admin->id])
            ->get('/admin');

        $response->assertOk();
        $content = $response->getContent();

        // Check that modal tags are NOT inside tbody
        preg_match_all('/<tbody>(.*?)<\/tbody>/s', $content, $matches);
        $this->assertNotEmpty($matches[1]);
        foreach ($matches[1] as $tbody) {
            $this->assertStringNotContainsString('class="modal', $tbody);
        }

        // Verify delete button has type="submit" and delete action for other users
        $otherUser = User::where('id', '!=', $admin->id)->firstOrFail();
        $response->assertSee(route('admin.users.destroy', $otherUser), false);
        $response->assertSee('type="submit"', false);
    }
}
