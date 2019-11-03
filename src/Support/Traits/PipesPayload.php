<?php

namespace MorningTrain\Laravel\Resources\Support\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait PipesPayload
{

    protected function payloadToCollection($payload)
    {

        if($payload instanceof Model) {
            return collect([$payload]);
        }

        if($payload instanceof Collection) {
            return $payload;
        }

        if(is_array($payload) && isset($payload['collection'])) {
            return $this->payloadToCollection($payload['collection']);
        }

        if(is_array($payload) && isset($payload['model'])) {
            return $this->payloadToCollection($payload['model']);
        }

        return collect([$payload]);
    }

    protected function payloadToModel($payload)
    {

        if($payload instanceof Model) {
            return $payload;
        }

        if($payload instanceof Collection) {
            return $this->payloadToModel($payload->first());
        }

        if(is_array($payload) && isset($payload['collection'])) {
            return $this->payloadToModel($payload['collection']);
        }

        if(is_array($payload) && isset($payload['model'])) {
            return $this->payloadToModel($payload['model']);
        }

        return $payload;
    }

}
