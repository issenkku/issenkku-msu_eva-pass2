<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $subjects = DB::table('subjects')->select(['id', 'code'])->orderBy('id')->get();
        $normalizedById = $subjects->mapWithKeys(fn ($subject) => [
            $subject->id => mb_strtoupper(trim((string) $subject->code), 'UTF-8'),
        ]);
        $duplicates = $normalizedById->groupBy(fn ($code) => $code)
            ->filter(fn ($ids) => $ids->count() > 1)
            ->keys();

        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException('Duplicate normalized subject codes: '.$duplicates->implode(', '));
        }

        DB::transaction(function () use ($normalizedById): void {
            foreach ($normalizedById as $id => $code) {
                DB::table('subjects')->where('id', $id)->update(['code' => $code]);
            }
        });

        Schema::table('subjects', function (Blueprint $table): void {
            $table->unique('code', 'subjects_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table): void {
            $table->dropUnique('subjects_code_unique');
        });
    }
};
