<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportScoreHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'support_criteria_id',
        'previous_achieved_score',
        'new_achieved_score',
        'previous_weighted_score',
        'new_weighted_score',
        'reason',
        'modifier_user_id',
        'modifier_role',
    ];

    protected $casts = [
        'previous_achieved_score' => 'decimal:2',
        'new_achieved_score' => 'decimal:2',
        'previous_weighted_score' => 'decimal:2',
        'new_weighted_score' => 'decimal:2',
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
