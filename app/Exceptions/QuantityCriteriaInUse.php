<?php

namespace App\Exceptions;

use RuntimeException;

class QuantityCriteriaInUse extends RuntimeException
{
    /**
     * @param  array{workload_forms: int, quantity_scores: int, quantity_score_histories: int}  $dependencies
     */
    public function __construct(private readonly array $dependencies)
    {
        parent::__construct('ไม่สามารถลบเกณฑ์ด้านปริมาณที่มีการใช้งานแล้ว');
    }

    /**
     * @return array{workload_forms: int, quantity_scores: int, quantity_score_histories: int}
     */
    public function dependencies(): array
    {
        return $this->dependencies;
    }
}
