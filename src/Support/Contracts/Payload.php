<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class Payload implements Responsable
{

    protected $_data = [];

    public function __construct(Operation $operation)
    {
        $this->operation = $operation;
    }

    public function set($path, $value)
    {
        Arr::set($this->_data, $path, $value);
    }

    public function get($path, $default = null)
    {
        return Arr::get($this->_data, $path, $default);
    }

    public function has($path)
    {
        return Arr::has($this->_data, $path);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function toResponse($request)
    {

        $response = $this->response;

        if(!$response) {

            $data = $this->data;

            if(!isset($data) || empty($data) || $data instanceof Operation) {
                $data = [];
            }

            if ($data instanceof Model) {
                $data = ['model' => $data];
            }

            if ($data instanceof Collection) {
                $data = ['collection' => $data];
            }

            if (!is_object($data) && !is_array($data)) {
                $data = [$data];
            }

            $response = $data;
        }

        if ($response instanceof \Exception) {
            throw $response;
        }

        if ($response instanceof Response || $response instanceof View) {
            return $response;
        }

        if (!is_array($response)) {
            return $response;
        }

        $status = $this->get('status_code', 200);
        $headers = [];
        $options = 0;

        if($this->has('meta')) {
            $response['meta'] = $this->meta;
        }

        if (request()->has('_request_uuid')) {
            $response['_request_uuid'] = request()->input('_request_uuid');
        }

        $response['message'] = $this->get('message', null);

        return response()->json($response, $status, $headers, $options);
    }

}
