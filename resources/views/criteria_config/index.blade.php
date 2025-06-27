<!-- resources/views/criteria/index.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">
                    ชื่อหน้า
                </h2>
                <a href="#" class="px-5 py-2 bg-lime-400 text-gray-800 font-semibold rounded-md hover:bg-lime-300">
                    เพิ่มเกณฑ์
                </a>
            </div>

            <hr class="mb-8">

            <!-- Criteria Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- ===== Start Mockup Data Card 1 ===== -->
                <div class="bg-gray-100 p-6 rounded-lg shadow-sm">
                    <h3 class="text-xl font-semibold text-gray-900">เกณฑ์ที่ 1 ฝ่ายวิชาการ</h3>
                    <p class="text-sm text-gray-600 mb-4">รายละเอียดสั้นๆ ของเกณฑ์นี้ (ถ้ามี)</p>
                    <div class="flex space-x-2">
                        <a href="#" class="flex-1 text-center px-4 py-2 bg-yellow-400 text-gray-800 rounded-md hover:bg-yellow-500">แก้ไข</a>
                        <a href="#" class="flex-1 text-center px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-500 hover:text-white">ลบ</a>
                    </div>
                </div>
                <!-- ===== End Mockup Data Card 1 ===== -->

                <!-- ===== Start Mockup Data Card 2 ===== -->
                <div class="bg-gray-100 p-6 rounded-lg shadow-sm">
                    <h3 class="text-xl font-semibold text-gray-900">เกณฑ์ที่ 2 ฝ่ายวิชาการ</h3>
                    <p class="text-sm text-gray-600 mb-4">รายละเอียดสั้นๆ ของเกณฑ์นี้ (ถ้ามี)</p>
                    <div class="flex space-x-2">
                        <a href="#" class="flex-1 text-center px-4 py-2 bg-yellow-400 text-gray-800 rounded-md hover:bg-yellow-500">แก้ไข</a>
                        <a href="#" class="flex-1 text-center px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-500 hover:text-white">ลบ</a>
                    </div>
                </div>
                <!-- ===== End Mockup Data Card 2 ===== -->

                <!-- ===== Start Mockup Data Card 3 ===== -->
                <div class="bg-gray-100 p-6 rounded-lg shadow-sm">
                    <h3 class="text-xl font-semibold text-gray-900">เกณฑ์ที่ 3 ฝ่ายบุคคล</h3>
                    <p class="text-sm text-gray-600 mb-4">รายละเอียดสั้นๆ ของเกณฑ์นี้ (ถ้ามี)</p>
                    <div class="flex space-x-2">
                        <a href="#" class="flex-1 text-center px-4 py-2 bg-yellow-400 text-gray-800 rounded-md hover:bg-yellow-500">แก้ไข</a>
                        <a href="#" class="flex-1 text-center px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-500 hover:text-white">ลบ</a>
                    </div>
                </div>
                <!-- ===== End Mockup Data Card 3 ===== -->

                <!-- ===== Start Mockup Data Card 4 ===== -->
                <div class="bg-gray-100 p-6 rounded-lg shadow-sm">
                    <h3 class="text-xl font-semibold text-gray-900">เกณฑ์ที่ 4 ฝ่ายการตลาด</h3>
                    <p class="text-sm text-gray-600 mb-4">รายละเอียดสั้นๆ ของเกณฑ์นี้ (ถ้ามี)</p>
                    <div class="flex space-x-2">
                        <a href="#" class="flex-1 text-center px-4 py-2 bg-yellow-400 text-gray-800 rounded-md hover:bg-yellow-500">แก้ไข</a>
                        <a href="#" class="flex-1 text-center px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-500 hover:text-white">ลบ</a>
                    </div>
                </div>
                <!-- ===== End Mockup Data Card 4 ===== -->

                <!-- ===== Start Mockup Data Card 5 ===== -->
                <div class="bg-gray-100 p-6 rounded-lg shadow-sm">
                    <h3 class="text-xl font-semibold text-gray-900">เกณฑ์ที่ 5 ฝ่ายบุคคล</h3>
                    <p class="text-sm text-gray-600 mb-4">รายละเอียดสั้นๆ ของเกณฑ์นี้ (ถ้ามี)</p>
                    <div class="flex space-x-2">
                        <a href="#" class="flex-1 text-center px-4 py-2 bg-yellow-400 text-gray-800 rounded-md hover:bg-yellow-500">แก้ไข</a>
                        <a href="#" class="flex-1 text-center px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-500 hover:text-white">ลบ</a>
                    </div>
                </div>
                <!-- ===== End Mockup Data Card 5 ===== -->

            </div>
        </div>
    </div>
@endsection