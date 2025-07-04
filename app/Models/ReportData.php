<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportData extends Model
{
    protected $table = 'report_datas';
    public $timestamps = false;

    protected $fillable = [
        'report_title',
        'report_description',
        'assessment_type',
        'comment',
        'criteria_version_id',
    ];

    public function criteriaVersion(): BelongsTo
    {
        return $this->belongsTo(CriteriaVersion::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Reports::class);
    }
}
