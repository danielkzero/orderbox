<?php

namespace App\Support;

use LogicException;

class CompanyContext
{
    private ?int $companyId = null;

    public function set(int $companyId): void
    {
        $this->companyId = $companyId;
    }

    public function id(): int
    {
        return $this->companyId ?? throw new LogicException('Company context was not initialized.');
    }
}
