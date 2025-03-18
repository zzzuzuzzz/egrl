<?php

namespace App\Http\Controllers;

use App\Contracts\Services\DocumentsServiceContract;
use App\Contracts\Services\ListOfShareholdersServiceContract;
use Illuminate\Http\Request;

class ListOfShareholdersController extends Controller
{
    public function makeListOfShareholders (
        Request $request,
        ListOfShareholdersServiceContract $listOfShareholdersService,
        DocumentsServiceContract $documentsService
    ) {
        $inn = $request->get('inn');
        dd($documentsService->getDocumentFromFNS($inn));
    }

}
