<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('department_name');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('faculty');
            $table->string('university');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('prefix');
            $table->string('name');
            $table->string('employee_id')->unique();
            $table->string('password');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('personnel_type');
            $table->text('bio')->nullable();
            $table->string('status');
            $table->foreignId('position_id')->constrained('positions');
            $table->foreignId('department_id')->constrained('departments');
            $table->rememberToken()->nullable();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            // $table->foreignId('user_id');
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('user_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('action');
            $table->timestamp('action_timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('user_histories');
    }
};
