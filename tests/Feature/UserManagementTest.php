<?php

namespace Tests\Feature;

use App\Enums\PeranUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_view_user_management_page(): void
    {
        $superadmin = $this->user(PeranUser::Superadmin);

        $this->actingAs($superadmin)
            ->get('/admin/users')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Index', false)
                ->has('users.data', 1));
    }

    public function test_admin_spmb_cannot_manage_users(): void
    {
        $admin = $this->user(PeranUser::AdminSpmb);

        $this->actingAs($admin)->get('/admin/users')->assertForbidden();
    }

    public function test_superadmin_can_create_admin_spmb(): void
    {
        $superadmin = $this->user(PeranUser::Superadmin);

        $this->actingAs($superadmin)->post('/admin/users', [
            'name' => 'Panitia Baru',
            'username' => 'panitia.baru',
            'email' => 'panitia@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'peran' => PeranUser::AdminSpmb->value,
        ])->assertRedirect();

        $user = User::where('username', 'panitia.baru')->firstOrFail();
        $this->assertSame(PeranUser::AdminSpmb, $user->peran);
        $this->assertTrue($user->status_aktif);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_superadmin_can_suspend_and_reactivate_admin(): void
    {
        $superadmin = $this->user(PeranUser::Superadmin);
        $admin = $this->user(PeranUser::AdminSpmb);

        $this->actingAs($superadmin)
            ->patch("/admin/users/{$admin->id}", ['action' => 'suspend'])
            ->assertRedirect();
        $this->assertFalse($admin->fresh()->status_aktif);

        $this->actingAs($superadmin)
            ->patch("/admin/users/{$admin->id}", ['action' => 'reactivate'])
            ->assertRedirect();
        $this->assertTrue($admin->fresh()->status_aktif);
    }

    public function test_superadmin_cannot_modify_own_account(): void
    {
        $superadmin = $this->user(PeranUser::Superadmin);

        $this->actingAs($superadmin)
            ->patch("/admin/users/{$superadmin->id}", ['action' => 'suspend'])
            ->assertSessionHasErrors('action');

        $this->assertTrue($superadmin->fresh()->status_aktif);
    }

    private function user(PeranUser $peran): User
    {
        return User::factory()->create([
            'username' => fake()->unique()->userName(),
            'peran' => $peran,
            'status_aktif' => true,
        ]);
    }
}
