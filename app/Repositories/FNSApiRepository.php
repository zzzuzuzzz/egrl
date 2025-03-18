<?php

namespace App\Repositories;

use App\Contracts\Repositories\FNSApiRepositoryContract;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class FNSApiRepository implements FNSApiRepositoryContract
{
    public function getDocument(int $inn): array
    {
        return Http::get('https://egrul.itsoft.ru/' . $inn . '.json')->throw()->json();
    }
}
