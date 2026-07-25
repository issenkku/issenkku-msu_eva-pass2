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
        'allow_activity_entries',
        'allow_evaluatee_indicator',
        'allow_evaluatee_weight',
        'group_activity_entries_by_indicator',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'weight' => 'decimal:2',
        'require_evidence' => 'boolean',
        'allow_activity_entries' => 'boolean',
        'allow_evaluatee_indicator' => 'boolean',
        'allow_evaluatee_weight' => 'boolean',
        'group_activity_entries_by_indicator' => 'boolean',
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

    public function activityEntries(): HasMany
    {
        return $this->hasMany(SupportActivityEntry::class);
    }

    public function indicatorItems(): HasMany
    {
        return $this->hasMany(SupportIndicatorItem::class)->orderBy('sequence');
    }
}
