<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use MorningTrain\Laravel\Resources\ResourceRepository;

class IsPermitted extends Pipe
{

    public function pipe()
    {
        if (!$this->operation->canExecute($this->data)) {
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
        if (!Auth::check()) {
            return [];
        }

        $user = Auth::user();

        $res = $collection->mapWithKeys(function ($model) use ($user) {

            if ($model === null || !($model instanceof Model)) {
                return [];
            }

            return [
                $model->getKey() =>
                    collect(ResourceRepository::getModelPermissions($model))
                        ->filter(function ($operation) use ($model, $user) {
                            return $user->can($operation, $model);
                        })
                        ->values()
                        ->all(),
            ];
        });

        return $res;
    }

}
