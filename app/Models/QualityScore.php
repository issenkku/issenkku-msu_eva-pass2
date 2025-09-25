<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityScore extends Model
{
    use HasFactory;

    protected $table = 'quality_scores';

    public $timestamps = true;

    protected $fillable = [
        'quality_sub_criteria_id',
        'user_id',
        'report_id',
        'score',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function qualitySubCriteria(): BelongsTo
    {
        return $this->belongsTo(QualitySubCriteria::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Reports::class);
    }
}
