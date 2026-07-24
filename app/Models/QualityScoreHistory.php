<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityScoreHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'quality_sub_criteria_id',
        'previous_score',
        'new_score',
        'reason',
        'modifier_user_id',
        'modifier_role',
    ];

    protected $casts = [
        'previous_score' => 'decimal:2',
        'new_score' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Reports::class, 'report_id');
    }

    public function subCriteria(): BelongsTo
    {
        return $this->belongsTo(QualitySubCriteria::class, 'quality_sub_criteria_id');
    }

    public function modifierUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modifier_user_id');
    }
}
