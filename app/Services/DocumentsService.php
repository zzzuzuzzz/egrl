<?php

namespace App\Services;

use App\Contracts\Repositories\DocumentsRepositoryContract;
use App\Contracts\Repositories\FNSApiRepositoryContract;
use App\Contracts\Services\DocumentsServiceContract;
use Illuminate\Http\Client\Response;

class DocumentsService implements DocumentsServiceContract
{
    public function __construct(
        private readonly DocumentsRepositoryContract $documentsRepository,
        private readonly FNSApiRepositoryContract $FNSApiRepository
    ) {
    }

    public function getDocumentFromFNS(int $inn): array
    {
        return $this->FNSApiRepository->getDocument($inn);
    }

    public function createDocument(): void
    {
        // TODO: Implement createDocument() method.
    }
}
