<?php

namespace App\Contracts\Repositories;

use App\Models\ListOfShareholders;

interface ListOfShareholdersRepositoryContract
{
    public function getListOfShareholders(): ListOfShareholders;

    public function create(array $fields): ListOfShareholders;
}
