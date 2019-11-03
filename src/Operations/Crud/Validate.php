<?php

namespace MorningTrain\Laravel\Resources\Operations\Crud;


use Illuminate\Http\Request;
use MorningTrain\Laravel\Resources\Support\Contracts\EloquentOperation;

class Validate extends EloquentOperation
{
    const ROUTE_METHOD = 'post';

    protected $strict = false;

    public function handle($model = null)
    {
        /** @var Request $request */
        $request = request();

        $this->performValidation($model, $request, !$this->strict);

        return [
            'model' => $request->all(),
        ];

    }

    public function prepare($parameters)
    {
        $this->data = $this->onEmptyModel();

        return $this->data;
    }

    public function strict(bool $val = null)
    {
        return $this->genericGetSet('strict', $val);
    }

    public function onEmptyModel()
    {
        return $this->getEmptyModelInstance();
    }

}
