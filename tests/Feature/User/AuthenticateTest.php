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

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'เธเธนเนเธเธฃเธดเธซเธฒเธฃ']);
        Role::create(['name' => 'เธเธฃเธฃเธกเธเธฒเธฃ']);
        Role::create(['name' => 'เธเธนเนเธเธฃเธฐเน€เธกเธดเธ']);
        Role::create(['name' => 'เธเธนเนเธฃเธฑเธเธเธฒเธฃเธเธฃเธฐเน€เธกเธดเธ']);
    }

    private function loginPage(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    private function loginPayload(array $overrides = []): array
    {
        $this->loginPage();

        return array_merge([
            '_token' => session()->token(),
            'employee_id' => 'EMP001',
            'password' => 'password123',
        ], $overrides);
    }

    private function ajaxHeaders(): array
    {
        return ['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'];
    }

    public function test_login_page_is_accessible()
    {
        $this->loginPage();
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

        $response = $this->post('/login', $this->loginPayload(), $this->ajaxHeaders());

        $response->assertStatus(200);
        $response->assertJsonStructure(['redirect']);
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_valid_credentials_via_plain_form_post()
    {
        $department = DepartmentFactory::new()->create();
        $position = PositionFactory::new()->create();
        $user = User::factory()->create([
            'employee_id' => 'EMP002',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);

        $response = $this->post('/login', $this->loginPayload([
            'employee_id' => 'EMP002',
        ]));

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password()
    {
        $department = DepartmentFactory::new()->create();
        $position = PositionFactory::new()->create();
        User::factory()->create([
            'employee_id' => 'EMP001',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);

        $response = $this->post('/login', $this->loginPayload([
            'password' => 'wrongpassword',
        ]), $this->ajaxHeaders());

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
        User::factory()->create([
            'employee_id' => 'EMP001',
            'password' => bcrypt('password123'),
            'status' => 'inactive',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);

        $response = $this->post('/login', $this->loginPayload(), $this->ajaxHeaders());

        $response->assertStatus(413);
        $response->assertJson(['message' => 'บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ']);
        $this->assertGuest();
    }

    public function test_login_is_throttled_after_multiple_failed_attempts()
    {
        $department = DepartmentFactory::new()->create();
        $position = PositionFactory::new()->create();
        User::factory()->create([
            'employee_id' => 'EMP001',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);

        RateLimiter::clear('emp001|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', $this->loginPayload([
                'password' => 'wrongpassword',
            ]), $this->ajaxHeaders());

            $response->assertStatus(401);
        }

        $response = $this->post('/login', $this->loginPayload([
            'password' => 'wrongpassword',
        ]), $this->ajaxHeaders());

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

        $user->assignRole('admin');

        $response = $this->post('/login', $this->loginPayload([
            'employee_id' => 'ADMIN001',
        ]), $this->ajaxHeaders());

        $response->assertStatus(200);
        $response->assertJson(['redirect' => '/dashboard']);
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

        $user->assignRole('เธเธนเนเธเธฃเธดเธซเธฒเธฃ');

        $response = $this->post('/login', $this->loginPayload([
            'employee_id' => 'MGR001',
        ]), $this->ajaxHeaders());

        $response->assertStatus(200);
        $response->assertJson(['redirect' => '/manager-dashboard']);
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

        $user->assignRole('เธเธฃเธฃเธกเธเธฒเธฃ');

        $response = $this->post('/login', $this->loginPayload([
            'employee_id' => 'DIR001',
        ]), $this->ajaxHeaders());

        $response->assertStatus(200);
        $response->assertJson(['redirect' => '/director-dashboard']);
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

        $user->assignRole('เธเธนเนเธเธฃเธฐเน€เธกเธดเธ');

        $response = $this->post('/login', $this->loginPayload([
            'employee_id' => 'EVA001',
        ]), $this->ajaxHeaders());

        $response->assertStatus(200);
        $response->assertJson(['redirect' => '/evaluator-dashboard']);
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

        $user->assignRole('เธเธนเนเธฃเธฑเธเธเธฒเธฃเธเธฃเธฐเน€เธกเธดเธ');

        $response = $this->post('/login', $this->loginPayload([
            'employee_id' => 'EVE001',
        ]), $this->ajaxHeaders());

        $response->assertStatus(200);
        $response->assertJson(['redirect' => '/evaluatee-dashboard']);
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

        $this->actingAs($user, 'web');
        $this->assertAuthenticatedAs($user);

        $this->get('/login');
        $token = session()->token();

        $response = $this->post('/logout', [
            '_token' => $token,
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success', \App\Support\FriendlyErrorPage::LOGOUT_MESSAGE);
        $this->assertGuest();
    }

    protected function tearDown(): void
    {
        RateLimiter::clear('emp001|127.0.0.1');
        parent::tearDown();
    }
}
