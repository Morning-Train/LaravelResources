<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Eloquent;

use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;
use MorningTrain\Laravel\Resources\Support\Pipes\TransformToView;
use MorningTrain\Laravel\Resources\Support\Traits\HasFilters;
use MorningTrain\Laravel\Resources\Support\Traits\HasModel;

class FetchesModel extends Pipe
{

    use HasFilters;
    use HasModel;

    protected function pipes()
    {
        return [
            QueryModel::create()->model($this->model)->filters($this->filters),
            ConstrainQueryToKey::create()->model($this->model),
            QueryToModel::create(),
            TransformToView::create()->appends($this->appends),
        ];
    }

}
