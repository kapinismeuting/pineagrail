<?php

use App\Livewire\Admin\Users\UserIndex;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function createAdminRoleFixtures(): void
{
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'superadmin']);
    Role::create(['name' => 'customer']);
}

test('admins cannot access role management', function () {
    createAdminRoleFixtures();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get(route('admin.roles.index'));

    $response->assertForbidden();
});

test('admins cannot see superadmin users or role options', function () {
    createAdminRoleFixtures();

    $admin = User::factory()->create(['name' => 'Regular Admin']);
    $admin->assignRole('admin');

    $superadmin = User::factory()->create([
        'name' => 'Hidden Superadmin',
        'email' => 'hidden-superadmin@example.com',
    ]);
    $superadmin->assignRole('superadmin');

    $response = $this->actingAs($admin)->get(route('admin.users.index'));

    $response->assertOk()
        ->assertDontSee($superadmin->name)
        ->assertDontSee($superadmin->email)
        ->assertDontSee('value="superadmin"');
});

test('admins cannot assign the superadmin role through the user action', function () {
    createAdminRoleFixtures();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->set('role', 'superadmin')
        ->call('createUser')
        ->assertStatus(403);
});

test('superadmins can access role management', function () {
    createAdminRoleFixtures();

    $superadmin = User::factory()->create();
    $superadmin->assignRole('superadmin');

    $response = $this->actingAs($superadmin)->get(route('admin.roles.index'));

    $response->assertOk();
});
