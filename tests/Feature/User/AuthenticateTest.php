<?php

namespace Tests\Feature\User;

use App\Models\User;
use Database\Factories\DepartmentFactory;
use Database\Factories\PositionFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthenticateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles for testing
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'ผู้บริหาร']);
        Role::create(['name' => 'กรรมการ']);
        Role::create(['name' => 'ผู้ประเมิน']);
        Role::create(['name' => 'ผู้รับการประเมิน']);
    }

    public function test_login_page_is_accessible()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $department = DepartmentFactory::new()->create();
        $position = PositionFactory::new()->create();
        $user = User::factory()->create([
            'employee_id' => 'EMP001',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);

        // Visit login page first to establish session
        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);

        // Extract CSRF token from the session or generate one
        $token = session()->token();

        $response = $this->post('/login', [
            '_token' => $token,
            'employee_id' => 'EMP001',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['redirect']);
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password()
    {
        $department = DepartmentFactory::new()->create();
        $position = PositionFactory::new()->create();
        $user = User::factory()->create([
            'employee_id' => 'EMP001',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);

        // Visit login page first to establish session
        $this->get('/login');
        $token = session()->token();

        $response = $this->post('/login', [
            '_token' => $token,
            'employee_id' => 'EMP001',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'กรุณากรอกหมายเลขประจำตัวและรหัสผ่านให้ถูกต้อง',
        ]);
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login()
    {
        $department = DepartmentFactory::new()->create();
        $position = PositionFactory::new()->create();
        $user = User::factory()->create([
            'employee_id' => 'EMP001',
            'password' => bcrypt('password123'),
            'status' => 'inactive',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);

        $this->get('/login');
        $token = session()->token();

        $response = $this->post('/login', [
            '_token' => $token,
            'employee_id' => 'EMP001',
            'password' => 'password123',
        ]);

        $response->assertStatus(413);
        $response->assertJson(['message' => 'บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ']);
        $this->assertGuest();
    }

    public function test_login_is_throttled_after_multiple_failed_attempts()
    {
        $department = DepartmentFactory::new()->create();
        $position = PositionFactory::new()->create();
        $user = User::factory()->create([
            'employee_id' => 'EMP001',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);

        // Clear any existing rate limits
        RateLimiter::clear('emp001|127.0.0.1');

        // Visit login page to establish session
        $this->get('/login');

        // Attempt login 5 times with wrong password (this should trigger throttling)
        for ($i = 0; $i < 5; $i++) {
            $token = session()->token();
            $response = $this->post('/login', [
                '_token' => $token,
                'employee_id' => 'EMP001',
                'password' => 'wrongpassword',
            ]);
            $response->assertStatus(401);
        }

        // 6th attempt should be throttled
        $token = session()->token();
        $response = $this->post('/login', [
            '_token' => $token,
            'employee_id' => 'EMP001',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
        $response->assertJson([
            'message' => 'คุณพยายามเข้าสู่ระบบมากเกินไป กรุณารอ 1 นาทีแล้วลองใหม่อีกครั้ง.',
        ]);
        $this->assertGuest();
    }

    public function test_login_redirects_admin_user_correctly()
    {
        $department = DepartmentFactory::new()->create();
        $position = PositionFactory::new()->create();
        $user = User::factory()->create([
            'employee_id' => 'ADMIN001',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);

        // Assign admin role
        $user->assignRole('admin');

        // Visit login page first to establish session
        $this->get('/login');
        $token = session()->token();

        $response = $this->post('/login', [
            '_token' => $token,
            'employee_id' => 'ADMIN001',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'redirect' => '/dashboard',
        ]);
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_redirects_manager_user_correctly()
    {
        $department = DepartmentFactory::new()->create();
        $position = PositionFactory::new()->create();
        $user = User::factory()->create([
            'employee_id' => 'MGR001',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);

        // Assign manager role
        $user->assignRole('ผู้บริหาร');

        // Visit login page first to establish session
        $this->get('/login');
        $token = session()->token();

        $response = $this->post('/login', [
            '_token' => $token,
            'employee_id' => 'MGR001',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'redirect' => '/manager-dashboard',
        ]);
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_redirects_director_user_correctly()
    {
        $department = DepartmentFactory::new()->create();
        $position = PositionFactory::new()->create();
        $user = User::factory()->create([
            'employee_id' => 'DIR001',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);

        // Assign director role
        $user->assignRole('กรรมการ');

        // Visit login page first to establish session
        $this->get('/login');
        $token = session()->token();

        $response = $this->post('/login', [
            '_token' => $token,
            'employee_id' => 'DIR001',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'redirect' => '/director-dashboard',
        ]);
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_redirects_evaluator_user_correctly()
    {
        $department = DepartmentFactory::new()->create();
        $position = PositionFactory::new()->create();
        $user = User::factory()->create([
            'employee_id' => 'EVA001',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);

        // Assign evaluator role
        $user->assignRole('ผู้ประเมิน');

        // Visit login page first to establish session
        $this->get('/login');
        $token = session()->token();

        $response = $this->post('/login', [
            '_token' => $token,
            'employee_id' => 'EVA001',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'redirect' => '/evaluator-dashboard',
        ]);
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_redirects_evaluatee_user_correctly()
    {
        $department = DepartmentFactory::new()->create();
        $position = PositionFactory::new()->create();
        $user = User::factory()->create([
            'employee_id' => 'EVE001',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);

        // Assign evaluatee role
        $user->assignRole('ผู้รับการประเมิน');

        // Visit login page first to establish session
        $this->get('/login');
        $token = session()->token();
        $response = $this->post('/login', [
            '_token' => $token,
            'employee_id' => 'EVE001',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'redirect' => '/evaluatee-dashboard',
        ]);
        $this->assertAuthenticatedAs($user);
    }

    public function test_logout_works_correctly()
    {
        $department = DepartmentFactory::new()->create();
        $position = PositionFactory::new()->create();
        $user = User::factory()->create([
            'employee_id' => 'EMP001',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);

        // Act as user
        $this->actingAs($user, 'web');

        $this->assertAuthenticatedAs($user);

        // Grab CSRF token from session
        $this->get('/login');
        $token = session()->token();

        // Perform logout with token
        $response = $this->post('/logout', [
            '_token' => $token,
        ]);

        // Assert redirect and flash message
        $response->assertRedirect('/login');
        $response->assertSessionHas('success', 'ออกจากระบบสำเร็จ');

        // Ensure user is logged out
        $this->assertGuest();
    }

    protected function tearDown(): void
    {
        // Clear rate limiter after each test
        RateLimiter::clear('emp001|127.0.0.1');
        parent::tearDown();
    }
}
