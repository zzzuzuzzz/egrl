<?php

namespace App\Repositories;

use App\Contracts\Repositories\DocumentsRepositoryContract;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;

class DocumentsRepository implements DocumentsRepositoryContract
{
    public function saveDocument(int $inn, array $document): Document
    {
        return Document::firstOrCreate([
            'user_id' => Auth::user()->id,
            'title' => $document['СвЮЛ']['СвНаимЮЛ']['СвНаимЮЛСокр']['@attributes']['НаимСокр'] . ' ' . date('Y-m-d'),
            'content' => json_encode($document),
            'inn' => $inn
        ]);
    }
}
