<?php

namespace App\Models;

use App\Support\Subjects\SubjectCode;
use App\Support\Subjects\SubjectName;
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

    protected function nameTh(): Attribute
    {
        return Attribute::make(set: fn (mixed $value): ?string => SubjectName::normalize($value));
    }

    protected function nameEn(): Attribute
    {
        return Attribute::make(set: fn (mixed $value): ?string => SubjectName::normalize($value));
    }

    protected function displayName(): Attribute
    {
        return Attribute::get(
            fn (): string => SubjectName::display($this->name_th, $this->name_en),
        );
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
