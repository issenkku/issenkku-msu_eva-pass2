<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'support_criteria_id',
        'achieved_score',
        'weighted_score',
        'modification_reason',
        'modifier_user_id',
        'modifier_role',
    ];

    protected $casts = [
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

    public function modifierUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modifier_user_id');
    }
}
