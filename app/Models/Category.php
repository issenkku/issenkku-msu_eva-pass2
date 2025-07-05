<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';
    public $timestamps = false;

    protected $fillable = [
        'main_categories',
        'sub_categories',
        'sequence',
        'criteria_version_id',
    ];

    public function criteriaVersion(): BelongsTo
    {
        return $this->belongsTo(CriteriaVersion::class);
    }

    public function evaluationLists()
    {
        return $this->hasMany(EvaluationList::class, 'categorie_id');
    }
    
}
