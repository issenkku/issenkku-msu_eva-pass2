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
        'reason',
        'modified_by',
        'modified_by_role',
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
