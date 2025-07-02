@extends('layouts.user-evaluation')

@section('title', 'Evaluation - ระบบประเมิน')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-evaluate-profile-card :user="$user"/>

    <x-quantity-table/>
    <x-quality-table/>

    <div class="flex justify-center gap-4 mt-8">
        <a href="{{ route('dashboard') }}" class="px-6 py-2 bg-white rounded-md text-center hover:bg-gray-200 w-40">กลับ</a>
        <button type="submit" class="bg-pink-400 text-white px-6 py-2 rounded-md hover:bg-pink-500 w-40">
            บันทึกร่าง
        </button>
        <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-md hover:bg-purple-700 w-40">
            ส่งแบบประเมิน
        </button>
    </div>
</div>

<!-- Mobile-friendly spacing -->
<style>
@media (max-width: 768px) {
    .space-y-6 > * + * {
        margin-top: 1rem;
    }
    
    .max-w-4xl {
        max-width: 100%;
        padding: 0 1rem;
    }
}
</style>
@endsection