@extends('layouts.app')
@section('content')
    @include('criteria_config.partials.evaluators-styles')
    <div class="py-12">
        <div class="max-w-6xl mx-auto px-4">
            <div class="bg-white shadow-sm rounded-lg p-6 mb-4">
                @include('criteria_config.partials.evaluators-period-section')

                <h2 class="text-xl font-semibold text-gray-800 mb-4">กำหนดผู้ประเมิน / ผู้รับการประเมิน</h2>

                {{-- ฟอร์ม --}}
                <form id="evaluation-form" action="#" method="POST">
                    <!-- CSRF Token -->
                    <input type="hidden" name="_token" value="csrf-token-here">

                    @include('criteria_config.partials.evaluators-filter-section')

                    <div class="space-y-8">
                        @include('criteria_config.partials.evaluators-evaluatees-section')
                        @include('criteria_config.partials.evaluators-reviewers-section')
                    </div>

                    @include('criteria_config.partials.evaluators-form-actions')
                </form>
            </div>
        </div>

        @include('criteria_config.partials.evaluators-script')
    </div>
@endsection
