<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use App\Models\License;
use App\Models\LicenseServer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityRemediationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->adminUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->adminUser->assignRole('admin');
    }

    public function test_security_headers_are_present_and_x_powered_by_is_removed(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Strict-Transport-Security');
        $response->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
        $response->assertHeader('Cross-Origin-Resource-Policy', 'same-origin');

        $this->assertFalse($response->headers->has('X-Powered-By'));
    }

    public function test_vendor_store_rejects_sql_injection_payloads(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.vendors.store'), [
                'name' => "superadmin@petrotech.id%' and 'f%'='f",
                'name_server' => 'server1',
                'status' => 'enable',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_grant_access_rejects_overflow_integers_without_500_error(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.licenses.access.grant'), [
                'username' => 'testuser',
                'server_id' => '18446744073709551617',
                'vendor_id' => '18446744073709551617',
                'license_ids' => ['18446744073709551617'],
                'status' => 'enable',
            ]);

        // Must fail with validation errors, NOT 500 Database Exception
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['server_id', 'vendor_id', 'license_ids.0']);
    }

    public function test_revoke_all_access_rejects_overflow_integers(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.licenses.access.revoke_all'), [
                'username' => 'testuser',
                'server_id' => '99999999999999999999',
                'vendor_id' => '99999999999999999999',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['server_id', 'vendor_id']);
    }

    public function test_export_logs_validates_vendor_id_properly(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.licenses.logs.export'), [
                'vendor_id' => '99999999999999999999',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['vendor_id']);
    }

    public function test_vm_management_rejects_overflow_assigned_user_id(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.vm-management.store'), [
                'vm_name' => 'vm-test-overflow',
                'os_type' => 'linux',
                'application_name' => 'Petrel',
                'status' => 'running',
                'assigned_user_id' => '99999999999999999999',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['assigned_user_id']);
    }

    public function test_usage_metrics_rejects_unauthorized_access(): void
    {
        $server = LicenseServer::create([
            'server_name' => 'Primary Server',
            'hostname' => 'primary-srv',
            'ip_address' => '10.0.0.1',
            'port' => '27000',
            'status' => 'online',
        ]);

        $vendor = Vendor::create([
            'name' => 'Petrel Test',
            'status' => 'enable',
        ]);

        $license = License::create([
            'license_name' => 'TEST_FEAT',
            'application_name' => 'Test App',
            'vendor_id' => $vendor->id,
            'license_server_id' => $server->id,
            'total_seats' => 5,
            'status' => 'enable',
            'expiry_date' => now()->addYear(),
            'created_by' => $this->adminUser->id,
        ]);

        $normalUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($normalUser)
            ->getJson(route('admin.licenses.usage_metrics', $license->id));

        $response->assertStatus(403);
    }
}
