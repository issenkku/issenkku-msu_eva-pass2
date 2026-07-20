<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportCriteria extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_list_id',
        'sequence',
        'activity_name',
        'indicator',
        'target_value',
        'weight',
        'require_evidence',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'weight' => 'decimal:2',
        'require_evidence' => 'boolean',
    ];

    public function evaluationList(): BelongsTo
    {
        return $this->belongsTo(EvaluationList::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(SupportScore::class);
    }

    public function scoreHistories(): HasMany
    {
        return $this->hasMany(SupportScoreHistory::class)->latest();
    }

    public function evidenceAnswers(): HasMany
    {
        return $this->hasMany(EvidenceAnswer::class);
    }
}
