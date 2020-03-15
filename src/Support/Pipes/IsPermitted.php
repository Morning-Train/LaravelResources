<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use MorningTrain\Laravel\Resources\ResourceRepository;

class IsPermitted extends Pipe
{

    public function pipe()
    {

        $data = $this->data;

        $data = $data instanceof Collection ?
            $data : collect([$data]);

        $operation_identifier = $this->operation->identifier();

        $is_permitted = $data->every(function ($model) use($operation_identifier) {
            return Gate::allows($operation_identifier, $model);
        });

        if(!$is_permitted) {
            $this->forbidden('Unable to perform operation');
        }
    }

    protected function after()
    {

        $data = $this->data;

        if($data instanceof Model) {
            $data = new Collection([$data]);
        }

        if($data instanceof Collection) {
            $this->payload->set('meta.permissions', $this->getPermissionsMeta($data));
        }

    }

    /////////////////////////////////
    /// Helpers
    /////////////////////////////////

    protected function getPermissionsMeta($collection)
    {

        $res = $collection->mapWithKeys(function ($model) {

            if ($model === null || !($model instanceof Model)) {
                return [];
            }

            return [
                $model->getKey() =>
                    collect(ResourceRepository::getModelPermissions($model))
                        ->filter(function ($operation) use ($model) {
                            return Gate::allows($operation, $model);
                        })
                        ->values()
                        ->all(),
            ];
        });

        return $res;
    }

}
