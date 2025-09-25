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
        Schema::table('quality_scores', function (Blueprint $table) {
            // เพิ่ม id column เป็น primary key
            $table->id()->first();

            // เพิ่ม user_id column
            $table->unsignedBigInteger('user_id')->nullable()->after('id');

            // เพิ่ม foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quality_scores', function (Blueprint $table) {
            // ลบ foreign key constraint
            $table->dropForeign(['user_id']);

            // ลบ user_id column
            $table->dropColumn('user_id');

            // ลบ id column
            $table->dropColumn('id');
        });
    }
};
