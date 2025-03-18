<?php

namespace App\Contracts\Services;

use Illuminate\Http\Client\Response;

interface DocumentsServiceContract
{
    public function getDocumentFromFNS (int $inn): array;

    public function createDocument(): void;
}
