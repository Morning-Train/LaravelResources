<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\View\View;

class ToResponse extends Pipe
{

    protected function buildModelPayload(Model $model)
    {
        $response = [
            'model' => $this->modelResponse($model)
        ];

        /// Add filter metadata to response
        $metadata = ["meta" => $this->operation()->getMeta()];

        $response = array_merge($response, $metadata);

        return $response;
    }

    protected function buildCollectionPayload(Collection $collection)
    {
        $response = [
            'collection' => $collection->map(function ($model) {
                return $this->modelResponse($model);
            })
        ];

        /// Add filter metadata to response
        $metadata = ["meta" => $this->operation()->getMeta()];

        if (is_array($metadata)) {
            $response = array_merge($response, $metadata);
        }

        return $response;
    }

    protected function modelResponse($model)
    {
        return $model;
    }

    protected function buildPayload($payload_data)
    {

        if ($payload_data instanceof Model) {
            return $this->buildModelPayload($payload_data);
        }

        if ($payload_data instanceof Collection) {
            return $this->buildCollectionPayload($payload_data);
        }

        if (!is_object($payload_data) && !is_array($payload_data)) {
            $payload_data = [$payload_data];
        }

        return $payload_data;
    }

    protected function isResponseable($maybeResponse)
    {
        return $maybeResponse instanceof Response || $maybeResponse instanceof View;
    }

    protected function isException($maybeException)
    {
        return $maybeException instanceof \Exception;
    }

    /**
     * @param \Exception $exception
     * @throws \Exception
     */
    protected function handleException(\Exception $exception)
    {
        throw $exception;
    }

    public function handle($data, Closure $next)
    {

        if ($this->isException($data)) {
            return $this->handleException($data);
        }

        if ($this->isResponseable($data)) {
            return $next($data);
        }

        $status = $this->operation()->getStatusCode();
        $headers = [];
        $options = 0;

        $res = $this->buildPayload($data);
        $res['message'] = $this->operation()->getMessage();

        $response = response()->json($res, $status, $headers, $options);

        return $next($response);
    }

}
