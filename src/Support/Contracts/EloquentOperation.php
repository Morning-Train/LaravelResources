<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use MorningTrain\Laravel\Fields\Traits\ValidatesFields;
use MorningTrain\Laravel\Filters\Filters\FilterCollection;
use MorningTrain\Laravel\Resources\Http\Controllers\ResourceController;
use MorningTrain\Laravel\Support\Traits\StaticCreate;

abstract class EloquentOperation extends Operation
{

}
