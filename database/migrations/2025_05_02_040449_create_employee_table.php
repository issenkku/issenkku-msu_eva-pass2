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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('prefix');
            $table->string('name');
            $table->foreignId('position_id');
            $table->foreignId('employee_type_id');
            $table->foreignId('faculty_id');
            $table->foreignId('department_id');
            $table->string('personal_number')->unique();
            $table->string('password');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('position_name');
            $table->string('description')->nullable();
            $table->boolean('status');
            $table->timestamps();
        });

        Schema::create('employee_types', function (Blueprint $table) {
            $table->id();
            $table->string('type_name');
            $table->string('description')->nullable();
            $table->boolean('status');
            $table->timestamps();
        });

        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->string('faculty_name');
            $table->string('description')->nullable();
            $table->boolean('status');
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('department_name');
            $table->string('description')->nullable();
            $table->boolean('status');
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->foreignId('employee_id')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('employee_types');
        Schema::dropIfExists('faculties');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('password_reset_tokens');
    }
};
