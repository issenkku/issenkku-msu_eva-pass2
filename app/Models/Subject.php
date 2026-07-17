<?php

namespace App\Models;

use App\Support\Subjects\SubjectCode;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_th',
        'name_en',
        'credits',
        'lecture_credits',
        'lab_credits',
        'self_study_credits',
        'sort_order',
        'is_active',
    ];

    protected function code(): Attribute
    {
        return Attribute::make(set: fn (mixed $value): string => SubjectCode::normalize($value));
    }
}
