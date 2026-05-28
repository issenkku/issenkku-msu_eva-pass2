<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuantityScoreHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'quantity_sub_criteria_id',
        'previous_score_c',
        'new_score_c',
        'previous_description',
        'new_description',
        'modifier_user_id',
        'modifier_role',
    ];

    protected $casts = [
        'previous_score_c' => 'decimal:2',
        'new_score_c' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Reports::class, 'report_id');
    }

    public function quantitySubCriteria(): BelongsTo
    {
        return $this->belongsTo(QuantitySubCriteria::class);
    }

    public function modifierUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modifier_user_id');
    }
}
