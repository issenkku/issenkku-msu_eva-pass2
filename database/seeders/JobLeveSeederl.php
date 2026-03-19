<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobLeveSeederl extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();

        $jobLevels = [
            'ปฏิบัติการ',
            'ชำนาญการ',
            'ชำนาญการพิเศษ',
            'เชี่ยวชาญ',
            'เชี่ยวชาญพิเศษ',
            'อาจารย์',
            'ผู้ช่วยศาสตราจารย์',
            'รองศาสตราจารย์',
            'ศาสตราจารย์',
            'ลูกจ้างชั่วคราว',
        ];

        DB::table('job_levels')->upsert(
            array_map(
                static fn (string $name) => [
                    'name' => $name,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ],
                $jobLevels
            ),
            ['name'],
            ['updated_at']
        );
    }
}
