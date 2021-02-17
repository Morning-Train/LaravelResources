<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Setup;

use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;
use MorningTrain\Laravel\Resources\Support\Traits\HasFilters;
use MorningTrain\Laravel\Resources\Support\Traits\HasModel;

class SetupFilters extends Pipe
{

    /////////////////////////////////
    /// Traits
    /////////////////////////////////

    use HasModel;
    use HasFilters;

    /////////////////////////////////
    /// Handle
    /////////////////////////////////

    public function pipe()
    {
        $this->payload->filters = $this->getFilters();
    }

    public function after()
    {
        $filters = $this->payload->filters;
        $this->payload->set('meta.filters', $this->getMetaForFilters($filters));
    }

}
