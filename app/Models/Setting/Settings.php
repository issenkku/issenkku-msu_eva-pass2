<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Settings extends Model
{
    protected $fillable = [
        'faculty',
        'university',
        'notification_days',
        'logo_path',
        'background_path',
        'use_white_background',
    ];

    protected $casts = [
        'use_white_background' => 'boolean',
    ];

    public $timestamps = false; // Assuming you don't want timestamps for this model

    public function getLogoUrlAttribute(): string
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            return asset('storage/'.$this->logo_path);
        }

        return asset('favicon-msu.png').'?v=1';
    }

    public function getBackgroundUrlAttribute(): string
    {
        if ($this->background_path && Storage::disk('public')->exists($this->background_path)) {
            return asset('storage/'.$this->background_path);
        }

        return asset('images/workload-background.jpg');
    }
}
