@extends('layouts.app')
{{-- หน้า list หลัก เหลือหน้าที่ render เป็นหลัก โดยรับข้อมูลที่จัดรูปจาก controller --}}

@section('title', 'จัดการรอบการประเมิน')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('assignment-data.partials.index-flash-messages')

    <div class="bg-gray-50 min-h-screen py-4">
        <div class="max-w-7xl mx-auto px-4">
            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-clipboard-list mr-3 text-blue-600"></i>
                            จัดการรอบการประเมิน
                        </h1>
                        <p class="text-gray-600 mt-1">ดูข้อมูลและจัดการรอบการประเมินทั้งหมด</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('assignment-data.create') }}"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center justify-center">
                            <i class="fas fa-plus mr-2"></i>สร้างรอบการประเมินใหม่
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">รายการรอบการประเมิน</h2>
                </div>

                @if ($assignmentData->count() > 0)
                    <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[160px]">
                                        ระยะเวลาประเมิน
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[220px]">
                                        เกณฑ์การประเมิน
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[200px]">
                                        ผู้ประเมิน
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[160px]">
                                        ผู้รับการประเมิน
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[120px]">
                                        สถานะ
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[140px]">
                                        การดำเนินการ
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach ($assignmentData as $assignmentRow)
                                    @include('assignment-data.partials.index-table-row', [
                                        'assignmentRow' => $assignmentRow,
                                    ])
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $assignmentData->links() }}
                    </div>
                @else
                    @include('assignment-data.partials.index-empty-state')
                @endif
            </div>
        </div>

        @include('assignment-data.partials.index-evaluatees-modal')
    </div>

    @include('assignment-data.partials.index-script')
    @include('assignment-data.partials.index-styles')
@endsection
