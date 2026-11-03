<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StoreProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(PermissionSeeder::class);

        // 1. Create main user & tenant
        $this->user = User::create([
            'name' => 'Store Pharmacist Admin',
            'email' => 'pharmacist@example.com',
            'password' => Hash::make('old_password123'),
            'status' => 1,
        ]);

        $this->tenant = Tenant::create([
            'user_id' => $this->user->id,
            'store_name' => 'Care Pharmacy BD',
            'subdomain' => 'carepharma',
            'subscription_status' => 'active',
            'status' => 'active',
        ]);

        // 2. Setup Spatie Roles & Permissions for tenant
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($this->tenant->id);

        $role = Role::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Store Owner',
            'guard_name' => 'web',
        ]);

        $role->givePermissionTo('Store Dashboard');
        $this->user->assignRole($role);
    }

    private function actingAsTenant(): self
    {
        session(['tenant_id' => $this->tenant->id]);
        return $this->actingAs($this->user);
    }

    public function test_guest_cannot_view_store_profile_page(): void
    {
        $response = $this->get(route('store.admin.profile.edit'));
        $response->assertRedirect(route('store.admin.login'));
    }

    public function test_authenticated_store_admin_can_view_profile_page(): void
    {
        $response = $this->actingAsTenant()->get(route('store.admin.profile.edit'));

        $response->assertOk();
        $response->assertViewIs('store_admin.pages.profile.index');
        $response->assertSee('Store Pharmacist Admin');
        $response->assertSee('pharmacist@example.com');
        $response->assertSee('Care Pharmacy BD');
        $response->assertSee('Store Owner');
    }

    public function test_store_admin_can_update_name_and_email(): void
    {
        $response = $this->actingAsTenant()->put(route('store.admin.profile.update'), [
            'name' => 'Updated Pharmacist Name',
            'email' => 'updated_pharmacist@example.com',
        ]);

        $response->assertRedirect(route('store.admin.profile.edit'));
        $response->assertSessionHas('success', 'Profile updated successfully.');

        $this->user->refresh();
        $this->assertEquals('Updated Pharmacist Name', $this->user->name);
        $this->assertEquals('updated_pharmacist@example.com', $this->user->email);
    }

    public function test_profile_update_fails_if_email_is_taken_by_another_user(): void
    {
        User::create([
            'name' => 'Another Admin',
            'email' => 'taken@example.com',
            'password' => Hash::make('password'),
            'status' => 1,
        ]);

        $response = $this->actingAsTenant()->put(route('store.admin.profile.update'), [
            'name' => 'Store Pharmacist Admin',
            'email' => 'taken@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_store_admin_can_upload_and_replace_avatar_image(): void
    {
        Storage::fake('public');

        $avatar = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->actingAsTenant()->put(route('store.admin.profile.update'), [
            'name' => 'Store Pharmacist Admin',
            'email' => 'pharmacist@example.com',
            'img' => $avatar,
        ]);

        $response->assertRedirect(route('store.admin.profile.edit'));
        $response->assertSessionHas('success', 'Profile updated successfully.');

        $this->user->refresh();
        $this->assertNotNull($this->user->img);
        Storage::disk('public')->assertExists($this->user->img);

        $oldPath = $this->user->img;

        // Replace avatar
        $newAvatar = UploadedFile::fake()->image('new_avatar.png', 250, 250);
        $this->actingAsTenant()->put(route('store.admin.profile.update'), [
            'name' => 'Store Pharmacist Admin',
            'email' => 'pharmacist@example.com',
            'img' => $newAvatar,
        ]);

        $this->user->refresh();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($this->user->img);
    }

    public function test_store_admin_can_update_password_with_valid_current_password(): void
    {
        $response = $this->actingAsTenant()->put(route('store.admin.profile.password'), [
            'current_password' => 'old_password123',
            'password' => 'new_secure_password456',
            'password_confirmation' => 'new_secure_password456',
        ]);

        $response->assertRedirect(route('store.admin.profile.edit'));
        $response->assertSessionHas('success', 'Password updated successfully.');

        $this->user->refresh();
        $this->assertTrue(Hash::check('new_secure_password456', $this->user->password));
    }

    public function test_password_update_fails_with_incorrect_current_password(): void
    {
        $response = $this->actingAsTenant()->put(route('store.admin.profile.password'), [
            'current_password' => 'wrong_password',
            'password' => 'new_secure_password456',
            'password_confirmation' => 'new_secure_password456',
        ]);

        $response->assertSessionHasErrors('current_password');

        $this->user->refresh();
        $this->assertTrue(Hash::check('old_password123', $this->user->password));
    }

    public function test_password_update_fails_when_confirmation_does_not_match(): void
    {
        $response = $this->actingAsTenant()->put(route('store.admin.profile.password'), [
            'current_password' => 'old_password123',
            'password' => 'new_secure_password456',
            'password_confirmation' => 'mismatched_password',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_update_fails_when_password_too_short(): void
    {
        $response = $this->actingAsTenant()->put(route('store.admin.profile.password'), [
            'current_password' => 'old_password123',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
