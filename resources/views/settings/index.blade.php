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

                            <form action="{{ route('settings.store') }}" method="POST" enctype="multipart/form-data" data-async-settings-form>
                                @csrf
                                @if($setting)
                                    <input type="hidden" name="id" value="{{ $setting->id }}">
                                @endif

                                @include('settings.partials.index-form-fields')

                                @if($setting)
                                    @include('settings.partials.index-info-box')
                                @else
                                    <div data-settings-info-region></div>
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

@endsection
