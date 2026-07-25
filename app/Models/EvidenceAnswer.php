<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenceAnswer extends Model
{
    use HasFactory;

    protected $table = 'evidence_answers';

    protected $fillable = [
        'evaluation_list_id',
        'quality_main_criteria_id',
        'report_id',
        'workload_entry_id',
        'support_criteria_id',
        'support_activity_entry_id',
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
        return $this->belongsTo(Reports::class);
    }

    public function qualityMainCriteria(): BelongsTo
    {
        return $this->belongsTo(QualityMainCriteria::class, 'quality_main_criteria_id');
    }

    public function workloadEntry(): BelongsTo
    {
        return $this->belongsTo(WorkloadEntry::class, 'workload_entry_id');
    }

    public function supportCriteria(): BelongsTo
    {
        return $this->belongsTo(SupportCriteria::class);
    }

    public function supportActivityEntry(): BelongsTo
    {
        return $this->belongsTo(SupportActivityEntry::class);
    }
}
