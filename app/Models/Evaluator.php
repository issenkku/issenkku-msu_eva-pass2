<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluator extends Model
{
    protected $table = 'evaluators';

    protected $fillable = [
        'assignment_id',
        'user_id',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignments::class, 'assignment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
