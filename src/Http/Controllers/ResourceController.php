<?php

namespace MorningTrain\Laravel\Resources\Http\Controllers;

use MorningTrain\Laravel\Resources\ResourceRepository;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;

class ResourceController
{

    /**
     * @param string $method
     * @param array $parameters
     * @return \Illuminate\Http\JsonResponse|mixed
     * @throws \Exception
     */
    public function executeOperation()
    {
        if(function_exists('start_measure')) {
            start_measure('execute_operation', 'Executing operation');
        }

        /// Magic method to catch all calls to this controller
        /// It allows us to dynamically route the request to a specific operation
        /// An operation in this case, is a sort of request -> response template
        /// A certain operation might be used be different resources (A collection of operations)
        $operation = ResourceRepository::getOperationForCurrentRoute();

        /// First we should validate to see if the requested method is valid
        /// If that is not the case, then we might assume that something is misconfigured
        if (($operation instanceof Operation) === false) {
            throw new \Exception("Tried to execute method, but it does not match an operation and is deemed invalid.");
        }

        /// We are ready to execute the operation
        $response = $operation->execute();

        if(function_exists('stop_measure')) {
            stop_measure('execute_operation');
        }

        return $response;
    }

}
