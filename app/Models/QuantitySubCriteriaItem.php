<?php

// ไฟล์คลาสของระบบ: app/Models/QuantitySubCriteriaItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuantitySubCriteriaItem extends Model
{
    use HasFactory;

    protected $table = 'quantity_sub_criteria_items';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'sequence',
        'score_a',
        'score_b',
        'description',
        'quantity_sub_criteria_group_id',
        'criteria_version_id',
        'evaluation_list_id',
        'require_subject',
    ];

    protected $casts = [
        'require_subject' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(QuantitySubCriteriaGroup::class, 'quantity_sub_criteria_group_id');
    }
}
