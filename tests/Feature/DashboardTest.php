<?php

use App\Models\Assignments;
use App\Models\AssignmentData;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\User;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    Role::create(['name' => 'admin']);
    $user = User::factory()->create();
    $user->assignRole('admin');
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});

test('dashboard table rows expose searchable report metadata and filter hooks', function () {
    Role::create(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $evaluatee = User::factory()->create();
    $reportData = ReportData::factory()->create([
        'report_title' => 'Annual Performance Plan',
    ]);
    $report = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
    ]);
    $assignmentData = AssignmentData::factory()->create();
    Assignments::factory()->create([
        'assignment_data_id' => $assignmentData->id,
        'report_id' => $report->id,
        'evaluatee_id' => $evaluatee->id,
    ]);

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertStatus(200)
        ->assertSee('role="search"', false)
        ->assertSee('data-auto-search-form', false)
        ->assertSee('data-auto-search-input', false)
        ->assertSee('aria-label=', false)
        ->assertSee('aria-controls="dashboardFilterPanel"', false)
        ->assertSee('aria-expanded="false"', false)
        ->assertSee('aria-hidden="true"', false)
        ->assertSee('data-reset-filters', false)
        ->assertDontSee('onclick="resetFilters()"', false)
        ->assertDontSee('data-overview-filter=', false)
        ->assertSee('role="radiogroup"', false)
        ->assertSee('aria-controls="year-filter-badge-panel"', false)
        ->assertSee('data-search-text=', false)
        ->assertSee('aria-pressed="true"', false)
        ->assertSee('Annual Performance Plan', false);
});
