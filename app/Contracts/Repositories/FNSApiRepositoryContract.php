<?php

namespace App\Contracts\Repositories;

use Illuminate\Http\Client\Response;

interface FNSApiRepositoryContract
{
    public function  getDocument(int $inn): array;
}
