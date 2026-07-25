<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportActivityEntryHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_activity_entry_id',
        'previous_content',
        'new_content',
        'previous_indicator',
        'new_indicator',
        'previous_weight',
        'new_weight',
        'previous_achieved_score',
        'new_achieved_score',
        'previous_weighted_score',
        'new_weighted_score',
        'reason',
        'modified_by',
        'modified_by_role',
    ];

    protected $casts = [
        'previous_weight' => 'decimal:2',
        'new_weight' => 'decimal:2',
        'previous_achieved_score' => 'decimal:2',
        'new_achieved_score' => 'decimal:2',
        'previous_weighted_score' => 'decimal:2',
        'new_weighted_score' => 'decimal:2',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(SupportActivityEntry::class, 'support_activity_entry_id');
    }

    public function modifierUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
