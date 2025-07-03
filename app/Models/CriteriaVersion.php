<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CriteriaVersion extends Model
{
    protected $table = 'criteria_versions';

    protected $fillable = [
        'version_name',
        'created_by',
    ];


    // Add this relation to get user info for created_by
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function quantityMainCriterias(): HasMany
    {
        return $this->hasMany(QuantityMainCriteria::class);
    }

    public function qualityMainCriterias(): HasMany
    {
        return $this->hasMany(QualityMainCriteria::class);
    }

    public function reportDatas(): HasMany
    {
        return $this->hasMany(ReportData::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function evaluationLists()
    {
        return $this->hasMany(EvaluationList::class, 'criteria_version_id');
    }
}
