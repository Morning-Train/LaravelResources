<?php

namespace MorningTrain\Laravel\Resources\Operations\Eloquent;

use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\TriggerOnModelsInCollection;
use MorningTrain\Laravel\Filters\Filters\Filter;
use MorningTrain\Laravel\Resources\Support\Pipes\Validates;

class MassAction extends Index
{

    const ROUTE_METHOD = 'POST';

    protected $trigger = null;

    public function trigger($value = null)
    {
        $this->trigger = $value;

        return $this;
    }

    public function getFilters()
    {
        return [
            Filter::create()->when('ids', function ($q, $ids) {
                $q->whereIn($this->getModelKeyName(), $ids);
            })
        ];
    }

    public function beforePipes()
    {
        return array_merge(parent::beforePipes(), [
            Validates::create()->rules([
                'ids' => 'required|array'
            ])
        ]);
    }

    protected function pipes()
    {
        return [
            TriggerOnModelsInCollection::create()->trigger($this->trigger)
        ];
    }

}