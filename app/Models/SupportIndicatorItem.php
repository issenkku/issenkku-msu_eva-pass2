<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportIndicatorItem extends Model
{
    protected $fillable = [
        'support_criteria_id',
        'sequence',
        'code',
        'description',
    ];

    public function supportCriteria(): BelongsTo
    {
        return $this->belongsTo(SupportCriteria::class);
    }

    public function activityEntries(): HasMany
    {
        return $this->hasMany(SupportActivityEntry::class);
    }
}
