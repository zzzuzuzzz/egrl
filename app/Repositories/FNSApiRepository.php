<?php

namespace App\Repositories;

use App\Contracts\Repositories\FNSApiRepositoryContract;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class FNSApiRepository implements FNSApiRepositoryContract
{
    public function getDocument(int $inn): array
    {
        return json_decode(json_encode(simplexml_load_string(Http::get('https://egrul.itsoft.ru/' . $inn . '.xml')->throw()->body())), true);
    }
}
