<?php

namespace App\Contracts\Services;

use App\Models\Document;
use Illuminate\Http\Client\Response;

interface DocumentsServiceContract
{
    public function getDocumentFromFNS (int $inn): array;

    public function saveDocument(int $inn, array $document): Document;
}
