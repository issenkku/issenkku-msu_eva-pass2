<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportActivityEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'support_criteria_id',
        'support_indicator_item_id',
        'sequence',
        'content',
        'indicator',
        'weight',
        'achieved_score',
        'weighted_score',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'achieved_score' => 'decimal:2',
        'weighted_score' => 'decimal:2',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Reports::class, 'report_id');
    }

    public function supportCriteria(): BelongsTo
    {
        return $this->belongsTo(SupportCriteria::class);
    }

    public function indicatorItem(): BelongsTo
    {
        return $this->belongsTo(SupportIndicatorItem::class, 'support_indicator_item_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(SupportActivityEntryHistory::class)->latest();
    }

    public function evidenceAnswers(): HasMany
    {
        return $this->hasMany(EvidenceAnswer::class, 'support_activity_entry_id')
            ->orderBy('id');
    }
}
