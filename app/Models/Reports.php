<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reports extends Model
{
    use HasFactory;

    protected $table = 'reports';

    protected $fillable = [
        'report_data_id',
        'status',
        'comment',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    public function evidenceAnswers()
    {
        return $this->hasMany(EvidenceAnswer::class, 'report_id');
    }

    public function assignments()
    {
        return $this->hasOne(Assignments::class, 'report_id');
    }
}
