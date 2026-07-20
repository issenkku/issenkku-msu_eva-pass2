<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reports extends Model
{
    use HasFactory;

    protected $table = 'reports';

    protected $fillable = [
        'report_data_id',
        'status',
        'comment',
        'evaluator_comment',
        'director_comment',
        'manager_comment',
        'support_score_total',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'support_score_total' => 'decimal:2',
    ];

    public function reportData(): BelongsTo
    {
        return $this->belongsTo(ReportData::class);
    }

    public function quantityScores()
    {
        return $this->hasMany(QuantityScore::class, 'report_id');
    }

    public function qualityScores()
    {
        return $this->hasMany(QualityScore::class, 'report_id');
    }

    public function supportScores(): HasMany
    {
        return $this->hasMany(SupportScore::class, 'report_id');
    }

    public function evidenceAnswers()
    {
        return $this->hasMany(EvidenceAnswer::class, 'report_id');
    }

    public function workloadEntries()
    {
        return $this->hasMany(WorkloadEntry::class, 'report_id');
    }

    public function assignments()
    {
        return $this->hasOne(Assignments::class, 'report_id');
    }

    public function syncCombinedComment(): void
    {
        $parts = [];

        if (filled($this->evaluator_comment)) {
            $parts[] = "ผู้ประเมิน:\n{$this->evaluator_comment}";
        }

        if (filled($this->director_comment)) {
            $parts[] = "กรรมการ:\n{$this->director_comment}";
        }

        if (filled($this->manager_comment)) {
            $parts[] = "ผู้บริหาร:\n{$this->manager_comment}";
        }

        $this->comment = ! empty($parts) ? implode("\n\n", $parts) : null;
    }
}
