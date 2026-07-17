<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->text('name_th')->nullable()->change();
            $table->text('name_en')->nullable()->change();
        });
    }

    public function down(): void
    {
        $cannotRestoreOldSchema = DB::table('subjects')
            ->select(['name_th', 'name_en'])
            ->cursor()
            ->contains(fn (object $subject): bool => $subject->name_th === null
                || mb_strlen((string) $subject->name_th) > 255
                || mb_strlen((string) ($subject->name_en ?? '')) > 255);

        if ($cannotRestoreOldSchema) {
            throw new RuntimeException('Cannot shrink subject names without losing English-only or long names.');
        }

        Schema::table('subjects', function (Blueprint $table) {
            $table->string('name_th', 255)->nullable(false)->change();
            $table->string('name_en', 255)->nullable()->change();
        });
    }
};
