<?php

// ไฟล์คลาสของระบบ: app/Models/EvaluationList.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationList extends Model
{
    use HasFactory;

    protected $table = 'evaluation_lists';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'sum_score',
        'sequence',
        'annotation',
        'categorie_id',
        'criteria_version_id',
    ];

    public function criteriaVersion(): BelongsTo
    {
        return $this->belongsTo(CriteriaVersion::class);
    }

    public function evidenceAnswers()
    {
        return $this->hasMany(EvidenceAnswer::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categorie_id');
    }

    public function quantitySubCriterias()
    {
        return $this->hasMany(QuantitySubCriteria::class, 'evaluation_list_id', 'id');
    }

    public function qualitySubCriterias()
    {
        return $this->hasMany(QualitySubCriteria::class, 'evaluation_list_id', 'id');
    }
}
