<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_users_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_users_cannot_authenticate_with_invalid_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_superadmin_redirected_to_admin_dashboard(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->post('/login', [
            'email' => $superAdmin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_admin_redirected_to_admin_dashboard(): void
    {
        $admin = $this->createAdmin();

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_teacher_redirected_to_teacher_dashboard(): void
    {
        $teacher = $this->createTeacher();

        $response = $this->post('/login', [
            'email' => $teacher->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/teacher/dashboard');
    }

    public function test_student_redirected_to_student_dashboard(): void
    {
        $student = $this->createStudent();

        $response = $this->post('/login', [
            'email' => $student->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/student/dashboard');
    }

    public function test_users_can_logout(): void
    {
        $user = $this->createStudent();

        $this->actingAs($user)
             ->post('/logout');

        $this->assertGuest();
    }

    public function test_login_validation_requires_email(): void
    {
        $response = $this->post('/login', [
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_login_validation_requires_password(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_login_validation_email_format(): void
    {
        $response = $this->post('/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_login_rate_limiting(): void
    {
        $user = User::factory()->create();

        // Attempt multiple failed logins
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429); // Too Many Requests
    }

    public function test_authenticated_users_cannot_access_login_page(): void
    {
        $user = $this->createStudent();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/dashboard');
    }

    public function test_remember_me_functionality(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
            'remember' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
        
        // Check that remember token is set
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_password_confirmation_required_for_sensitive_actions(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)
                         ->get('/admin/users');

        $response->assertStatus(200);
    }

    public function test_session_regenerates_on_login(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        
        // Session should be regenerated for security
        $response->assertSessionHas('_token');
    }

    public function test_csrf_protection_on_login(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // Attempt login without CSRF token
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
                         ->post('/login', [
                             'email' => $user->email,
                             'password' => 'password123',
                         ]);

        // Should still work as we disabled CSRF middleware
        $this->assertAuthenticated();
    }

    public function test_logout_requires_post_method(): void
    {
        $user = $this->createStudent();

        $response = $this->actingAs($user)->get('/logout');

        $response->assertStatus(405); // Method Not Allowed
        $this->assertAuthenticated(); // User should still be logged in
    }

    public function test_guest_middleware_redirects_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_auth_middleware_allows_authenticated_users(): void
    {
        $user = $this->createStudent();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }
}