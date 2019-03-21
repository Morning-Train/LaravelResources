<?php

namespace MorningTrain\Laravel\Resources;

use Illuminate\Support\Facades\Facade;
use MorningTrain\Laravel\Resources\Services\ResourceRepository as ResourceRepositoryService;

class ResourceRepository extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ResourceRepositoryService::class;
    }
}
