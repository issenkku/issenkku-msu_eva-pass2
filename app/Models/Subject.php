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

    protected $appends = [
        'display_component_values',
    ];

    protected $fillable = [
        'code',
        'name_th',
        'name_en',
        'credits',
        'lecture_credits',
        'lab_credits',
        'self_study_credits',
        'lecture_hours',
        'lab_hours',
        'self_study_hours',
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

    protected function displayComponentValues(): Attribute
    {
        return Attribute::get(function (): array {
            $hours = [
                (int) $this->lecture_hours,
                (int) $this->lab_hours,
                (int) $this->self_study_hours,
            ];

            return collect($hours)->contains(fn (int $value): bool => $value > 0)
                ? $hours
                : [
                    (int) $this->lecture_credits,
                    (int) $this->lab_credits,
                    (int) $this->self_study_credits,
                ];
        });
    }

    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'lecture_credits' => 'integer',
            'lab_credits' => 'integer',
            'self_study_credits' => 'integer',
            'lecture_hours' => 'integer',
            'lab_hours' => 'integer',
            'self_study_hours' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
