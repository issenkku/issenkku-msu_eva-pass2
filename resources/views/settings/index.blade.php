@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views/settings/index.blade.php --}}
@section('content')
    @include('settings.partials.index-styles')

    {{-- หน้าตั้งค่าข้อมูลมหาวิทยาลัย: style, header, flash, form และ info box ถูกแยกตามหน้าที่ --}}
    <div class="form-container {{ $setting?->use_white_background ? 'is-white-background' : '' }}" style="--settings-background-image: url('{{ $setting?->background_url ?? asset('images/workload-background.jpg') }}');">
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
            const backgroundInput = document.getElementById('background');
            const backgroundPreview = document.getElementById('backgroundPreview');
            const removeBackgroundInput = document.querySelector('input[name="remove_background"]');
            const useWhiteBackgroundInput = document.getElementById('useWhiteBackground');
            const formContainer = document.querySelector('.form-container');
            const appBackgroundShell = document.querySelector('.app-background-shell');

            bindImagePreview(logoInput, logoPreview, removeLogoInput, 'defaultLogo');
            bindImagePreview(backgroundInput, backgroundPreview, removeBackgroundInput, 'defaultBackground', function (imageUrl) {
                if (formContainer) {
                    formContainer.style.setProperty('--settings-background-image', `url('${imageUrl}')`);
                    formContainer.classList.remove('is-white-background');
                }

                if (appBackgroundShell) {
                    appBackgroundShell.style.setProperty('--app-background-image', `url('${imageUrl}')`);
                    appBackgroundShell.classList.remove('is-white-background');
                }

                if (useWhiteBackgroundInput) {
                    useWhiteBackgroundInput.checked = false;
                }
            });

            if (useWhiteBackgroundInput) {
                useWhiteBackgroundInput.addEventListener('change', function () {
                    formContainer?.classList.toggle('is-white-background', useWhiteBackgroundInput.checked);
                    appBackgroundShell?.classList.toggle('is-white-background', useWhiteBackgroundInput.checked);
                });
            }

            function bindImagePreview(input, preview, removeInput, defaultKey, onChange = null) {
                if (!input || !preview) {
                    return;
                }

                input.addEventListener('change', function () {
                    const file = input.files?.[0];

                    if (!file) {
                        return;
                    }

                    if (removeInput) {
                        removeInput.checked = false;
                    }

                    const imageUrl = URL.createObjectURL(file);
                    preview.src = imageUrl;

                    if (onChange) {
                        onChange(imageUrl);
                    }
                });

                if (removeInput) {
                    removeInput.addEventListener('change', function () {
                        if (removeInput.checked) {
                            input.value = '';
                            preview.src = preview.dataset[defaultKey];

                            if (onChange) {
                                onChange(preview.dataset[defaultKey]);
                            }
                        }
                    });
                }
            }
        });
    </script>
@endsection
