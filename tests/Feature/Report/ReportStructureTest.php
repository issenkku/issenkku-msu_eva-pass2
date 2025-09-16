<?php


namespace Tests\Feature\Report;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting\Settings;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportStructureTest extends TestCase
{
    use RefreshDatabase;
}
