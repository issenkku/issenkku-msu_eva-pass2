<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('education_history')->nullable()->after('bio');
        });

        DB::table('users')
            ->whereNotNull('bio')
            ->where('bio', '!=', '')
            ->update([
                'education_history' => DB::raw("JSON_ARRAY(JSON_OBJECT('graduation_year', NULL, 'degree', bio, 'university', NULL))"),
            ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('education_history');
        });
    }
};
