<?php

namespace App\Support;

class AdminDashboardData
{
    public function __construct(private readonly array $viewData) {}

    public function toViewData(): array
    {
        return $this->viewData;
    }
}
