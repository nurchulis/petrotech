<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SettingsProfileSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);

        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $this->user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'department' => 'IT Dept',
            'phone' => '+62812345678',
            'employee_id' => 'EMP-001',
            'is_active' => true,
        ]);
        $this->user->assignRole('user');

        $this->otherUser = User::factory()->create([
            'email' => 'other@example.com',
            'employee_id' => 'EMP-002',
        ]);
        $this->otherUser->assignRole('user');
    }

    /**
     * Helper to invoke SettingsController@updateProfile directly via route or test closure
     */
    private function postUpdateProfile(array $data, ?User $actingUser = null)
    {
        // Define route dynamically if currently commented out in web.php
        if (!Route::has('settings.profile')) {
            Route::patch('/settings/profile', [\App\Http\Controllers\SettingsController::class, 'updateProfile'])
                ->middleware(['web', 'auth'])
                ->name('settings.profile');
        }

        $req = $actingUser ? $this->actingAs($actingUser) : $this;
        return $req->patch('/settings/profile', $data);
    }

    public function test_unauthenticated_user_cannot_update_profile(): void
    {
        $response = $this->postUpdateProfile([
            'name' => 'Hacker Name',
            'email' => 'hacker@example.com',
        ], null);

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_update_profile_with_valid_data(): void
    {
        $response = $this->postUpdateProfile([
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'department' => 'Operations',
            'phone' => '+6289999999',
        ], $this->user);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->user->refresh();
        $this->assertSame('Updated Name', $this->user->name);
        $this->assertSame('updated@example.com', $this->user->email);
        $this->assertSame('Operations', $this->user->department);
        $this->assertSame('+6289999999', $this->user->phone);
    }

    public function test_profile_update_keeps_same_email_without_validation_error(): void
    {
        $response = $this->postUpdateProfile([
            'name' => 'Same Email Test',
            'email' => 'original@example.com',
            'department' => 'Engineering',
            'phone' => '+6281111111',
        ], $this->user);

        $response->assertSessionHasNoErrors();
        $this->user->refresh();
        $this->assertSame('original@example.com', $this->user->email);
        $this->assertSame('Same Email Test', $this->user->name);
    }

    public function test_profile_update_rejects_duplicate_email(): void
    {
        $response = $this->postUpdateProfile([
            'name' => 'Duplicate Email Try',
            'email' => 'other@example.com', // Already owned by $this->otherUser
        ], $this->user);

        $response->assertSessionHasErrors(['email']);
        $this->user->refresh();
        $this->assertSame('original@example.com', $this->user->email);
    }

    public function test_profile_update_validates_required_and_maximum_string_lengths(): void
    {
        // 1. Missing required name and invalid email format
        $response = $this->postUpdateProfile([
            'name' => '',
            'email' => 'not-an-email',
        ], $this->user);

        $response->assertSessionHasErrors(['name', 'email']);

        // 2. Exceeding max lengths (name > 255, phone > 50)
        $response2 = $this->postUpdateProfile([
            'name' => str_repeat('A', 256),
            'email' => 'valid@example.com',
            'phone' => str_repeat('1', 51),
            'department' => str_repeat('D', 256),
        ], $this->user);

        $response2->assertSessionHasErrors(['name', 'phone', 'department']);
    }

    public function test_mass_assignment_protection_prevents_tampering_sensitive_fields(): void
    {
        // Attacker attempts to change role, employee_id, is_active, password, and permissions
        $response = $this->postUpdateProfile([
            'name' => 'Legit Name',
            'email' => 'legit@example.com',
            'department' => 'Security Team',
            'phone' => '+628123456789',
            // Injected unauthorized / sensitive fields:
            'is_active' => false,
            'is_admin' => 1,
            'role' => 'admin',
            'employee_id' => 'HACKED-999',
            'password' => 'new-secret-password',
        ], $this->user);

        $response->assertSessionHasNoErrors();

        $this->user->refresh();
        $this->assertSame('Legit Name', $this->user->name);
        $this->assertSame('Security Team', $this->user->department);

        // Verify sensitive fields remained completely untouched:
        $this->assertTrue((bool)$this->user->is_active, 'is_active must not be modified by profile update');
        $this->assertSame('EMP-001', $this->user->employee_id, 'employee_id must not be modified by profile update');
        $this->assertTrue($this->user->hasRole('user'));
        $this->assertFalse($this->user->hasRole('admin'), 'User role must not be escalated to admin');
    }

    public function test_sql_injection_payload_is_treated_as_literal_string_without_syntax_error(): void
    {
        $sqliPayload = "test' OR '1'='1' -- /*";

        $response = $this->postUpdateProfile([
            'name' => $sqliPayload,
            'email' => 'valid_sqli_test@example.com',
            'department' => "Dept' UNION SELECT 1,2,3--",
            'phone' => "0812'--",
        ], $this->user);

        // PDO parameter binding safely stores literal strings without SQL syntax error
        $response->assertSessionHasNoErrors();

        $this->user->refresh();
        $this->assertSame($sqliPayload, $this->user->name);
        $this->assertSame("Dept' UNION SELECT 1,2,3--", $this->user->department);
    }
}
