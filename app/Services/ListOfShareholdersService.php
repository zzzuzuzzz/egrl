<?php

namespace App\Services;
use App\Contracts\Repositories\ListOfShareholdersRepositoryContract;
use App\Contracts\Services\ListOfShareholdersServiceContract;

class ListOfShareholdersService implements ListOfShareholdersServiceContract
{
    public function __construct(
        ListOfShareholdersRepositoryContract $listOfShareholdersRepository,
    ) {
    }

    public function makeListOfShareholders(): void
    {
        // TODO: Implement getListOfShareholders() method.
    }
}
