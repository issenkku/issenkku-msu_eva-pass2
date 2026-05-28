@extends('layouts.app')
@section('content')
    @include('settings.partials.index-styles')

    {{-- หน้าตั้งค่าเว็บไซต์: style, header, flash, form และ info box ถูกแยกตามหน้าที่ --}}
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
            const selectedBackgroundPathInput = document.getElementById('selectedBackgroundPath');
            const deletedBackgroundInputs = document.getElementById('deletedBackgroundInputs');
            const backgroundLibraryItems = document.querySelectorAll('.background-library-item');
            const formContainer = document.querySelector('.form-container');
            const appBackgroundShell = document.querySelector('.app-background-shell');

            bindImagePreview(logoInput, logoPreview, removeLogoInput, 'defaultLogo');
            bindImagePreview(backgroundInput, backgroundPreview, removeBackgroundInput, 'defaultBackground', function (imageUrl) {
                setBackgroundImage(imageUrl);

                if (useWhiteBackgroundInput) {
                    useWhiteBackgroundInput.checked = false;
                }

                clearSelectedBackground();
            });

            backgroundLibraryItems.forEach(function (item) {
                const deleteButton = item.querySelector('.background-library-delete');

                item.addEventListener('click', function () {
                    selectBackgroundItem(item);
                });

                item.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        selectBackgroundItem(item);
                    }
                });

                if (deleteButton) {
                    deleteButton.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        markBackgroundForDelete(item);
                    });
                }
            });

            if (useWhiteBackgroundInput) {
                useWhiteBackgroundInput.addEventListener('change', function () {
                    formContainer?.classList.toggle('is-white-background', useWhiteBackgroundInput.checked);
                    appBackgroundShell?.classList.toggle('is-white-background', useWhiteBackgroundInput.checked);
                });
            }

            function selectBackgroundItem(item) {
                const imageUrl = item.dataset.backgroundUrl;

                if (!imageUrl || item.classList.contains('is-pending-delete')) {
                    return;
                }

                if (selectedBackgroundPathInput) {
                    selectedBackgroundPathInput.value = item.dataset.backgroundPath || '';
                }

                if (backgroundInput) {
                    backgroundInput.value = '';
                }

                if (removeBackgroundInput) {
                    removeBackgroundInput.checked = false;
                }

                if (useWhiteBackgroundInput) {
                    useWhiteBackgroundInput.checked = false;
                }

                if (backgroundPreview) {
                    backgroundPreview.src = imageUrl;
                }

                setBackgroundImage(imageUrl);

                backgroundLibraryItems.forEach(function (libraryItem) {
                    libraryItem.classList.toggle('is-active', libraryItem === item);
                });
            }

            function markBackgroundForDelete(item) {
                const backgroundPath = item.dataset.backgroundPath;

                if (!backgroundPath || item.classList.contains('is-pending-delete')) {
                    return;
                }

                item.classList.add('is-pending-delete');
                item.style.display = 'none';

                if (deletedBackgroundInputs) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'delete_background_paths[]';
                    input.value = backgroundPath;
                    deletedBackgroundInputs.appendChild(input);
                }

                if (selectedBackgroundPathInput?.value === backgroundPath || item.classList.contains('is-active')) {
                    clearSelectedBackground();

                    if (backgroundPreview?.dataset.defaultBackground) {
                        backgroundPreview.src = backgroundPreview.dataset.defaultBackground;
                        setBackgroundImage(backgroundPreview.dataset.defaultBackground);
                    }
                }
            }

            function setBackgroundImage(imageUrl) {
                if (formContainer) {
                    formContainer.style.setProperty('--settings-background-image', `url('${imageUrl}')`);
                    formContainer.classList.remove('is-white-background');
                }

                if (appBackgroundShell) {
                    appBackgroundShell.style.setProperty('--app-background-image', `url('${imageUrl}')`);
                    appBackgroundShell.classList.remove('is-white-background');
                }
            }

            function clearSelectedBackground() {
                if (selectedBackgroundPathInput) {
                    selectedBackgroundPathInput.value = '';
                }

                backgroundLibraryItems.forEach(function (item) {
                    item.classList.remove('is-active');
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
