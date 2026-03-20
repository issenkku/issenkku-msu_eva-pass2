<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityMainCriteria extends Model
{
    use HasFactory;

    protected $table = 'quality_main_criterias';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'ratio',
        'tooltips',
        'sequence',
        'require_evidence',
        'criteria_version_id',
    ];

    protected $casts = [
        'require_evidence' => 'boolean',
    ];

    public function criteriaVersion(): BelongsTo
    {
        return $this->belongsTo(CriteriaVersion::class);
    }

    public function qualitySubCriterias()
    {
        return $this->hasMany(QualitySubCriteria::class, 'quality_main_criteria_id');
    }

    // helper function to get the main criteria name
    public function mainCriteria()
    {
        return $this->belongsTo(QualityMainCriteria::class, 'quality_main_criteria_id');
    }
}
