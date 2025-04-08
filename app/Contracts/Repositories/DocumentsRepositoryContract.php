<?php

namespace App\Contracts\Repositories;

use App\Models\Document;

interface DocumentsRepositoryContract
{
    public function saveDocument(int $inn, array $document): Document;
}
