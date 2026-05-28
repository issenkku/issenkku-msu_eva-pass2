<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuantityScore extends Model
{
    use HasFactory;

    protected $table = 'quantity_scores';

    protected $primaryKey = 'id';

    protected $fillable = [
        'quantity_sub_criteria_id',
        'report_id',
        'score_C',
        'score_D',
        'description',
        'modifier_user_id',
        'modifier_role',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function quantitySubCriteria(): BelongsTo
    {
        return $this->belongsTo(QuantitySubCriteria::class);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function modifierUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modifier_user_id');
    }
}
