@extends('layouts.user-management-page')

@section('content')
<div class="min-h-screen flex justify-center items-center bg-neutral-50">
    <div class="w-full max-w-md px-4">
        <div class="shadow-figma border bg-white rounded-lg">
            <div class="space-y-4 text-center pb-8 p-6">
                <div class="mx-auto w-20 h-20 rounded-full flex items-center justify-center shadow-lg">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-2xl shadow-yellow-50">
                        <img src="{{ asset('favicon-msu.png') }}" alt="logo" />
                    </div>
                </div>
                <div class="space-y-2">
                    <h2 class="text-2xl font-bold text-gray-800">เข้าสู่ระบบ</h2>
                    <p class="text-gray-600 text-base">กรุณาป้อนข้อมูลเพื่อเข้าสู่ระบบประเมินผล</p>
                </div>
            </div>

            <div class="p-6 space-y-6">
                @if(session('success'))
                    <div class="text-green-600 font-semibold text-center">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="text-sm text-center font-semibold text-red-600 border border-red-200 bg-red-50 rounded-md p-3">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div class="space-y-2">
                        <label for="employee_id" class="text-sm font-medium text-gray-700">
                            Username
                        </label>
                        <div class="relative w-full">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A8 8 0 1116.879 6.196M15 12h.01" />
                            </svg>
                            <input
                                type="text"
                                id="employee_id"
                                name="employee_id"
                                value="{{ old('employee_id') }}"
                                placeholder="Username"
                                class="w-full h-12 pl-10 pr-4 border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-300 focus:border-gray-300 transition duration-150"
                                required
                            />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <div class="relative w-full">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.654 0 3-1.346 3-3S13.654 5 12 5 9 6.346 9 8s1.346 3 3 3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21H7a2 2 0 01-2-2v-2a6 6 0 0112 0v2a2 2 0 01-2 2z" />
                            </svg>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Password"
                                class="w-full h-12 pl-10 pr-4 border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-300 focus:border-gray-300 transition duration-150"
                                required
                            />
                        </div>
                    </div>

                    <div class="text-right">
                        <a href="#" class="text-publicHealth hover:text-rose-600 text-sm">ลืมรหัสผ่าน?</a>
                    </div>

                    <x-button type="primary" text="เข้าสู่ระบบ" buttonType="submit" class="w-full" />
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
