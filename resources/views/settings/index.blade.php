@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views/settings/index.blade.php --}}
@section('content')
    @include('settings.partials.index-styles')

    {{-- หน้าตั้งค่าข้อมูลมหาวิทยาลัย: style, header, flash, form และ info box ถูกแยกตามหน้าที่ --}}
    <div class="form-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="card card-custom">
                        @include('settings.partials.index-header')

                        <div class="card-body p-5">
                            @include('settings.partials.index-flash-message')

                            <form action="{{ route('settings.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @if($setting)
                                    <input type="hidden" name="id" value="{{ $setting->id }}">
                                @endif

                                @include('settings.partials.index-form-fields')

                                @if($setting)
                                    @include('settings.partials.index-info-box')
                                @endif

                                @include('settings.partials.index-form-actions')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Font Awesome ใช้สำหรับ icon ในส่วนหัว ฟอร์ม และกล่องข้อมูล --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const logoInput = document.getElementById('logo');
            const logoPreview = document.getElementById('logoPreview');
            const removeLogoInput = document.querySelector('input[name="remove_logo"]');

            if (!logoInput || !logoPreview) {
                return;
            }

            logoInput.addEventListener('change', function () {
                const file = logoInput.files?.[0];

                if (!file) {
                    return;
                }

                if (removeLogoInput) {
                    removeLogoInput.checked = false;
                }

                logoPreview.src = URL.createObjectURL(file);
            });

            if (removeLogoInput) {
                removeLogoInput.addEventListener('change', function () {
                    if (removeLogoInput.checked) {
                        logoInput.value = '';
                        logoPreview.src = logoPreview.dataset.defaultLogo;
                    }
                });
            }
        });
    </script>
@endsection
