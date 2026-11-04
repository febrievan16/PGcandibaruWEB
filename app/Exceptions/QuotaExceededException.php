<?php

namespace App\Exceptions;

use RuntimeException;

class QuotaExceededException extends RuntimeException
{
    public function __construct(
        string $message = 'Kuota sesi ini tidak mencukupi atau sudah habis. Silakan pilih sesi lain.',
        public int $remainingQuota = 0,
    ) {
        parent::__construct($message, 409);
    }
}

