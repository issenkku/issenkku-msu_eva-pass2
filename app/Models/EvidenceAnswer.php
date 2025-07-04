<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenceAnswer extends Model
{
    // public $incrementing = false;
    protected $table = 'evidence_answers';
    protected $fillable = [
        'evaluation_list_id',
        'report_id',
        'link',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function evaluationList(): BelongsTo
    {
        return $this->belongsTo(EvaluationList::class);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
