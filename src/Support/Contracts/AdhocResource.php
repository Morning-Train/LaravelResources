<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

class AdhocResource extends Resource
{

    /**
     * Internally set operation to use on this adhoc resource
     * It is currently not supported to override the name
     * The class name of the operation will be used as the name
     * @param $operation
     */
    public function withOperation($operation) {
        $this->_operations = [$operation];
    }

    /**
     * This is a override of the identifier method from the base resource class
     * The nested operation identifier logic has been removed
     * It will always return an identifier matching the resource identifier - even for its operations
     * This means that the resource can only support a single operation
     * And it means that there could be potential naming conflicts with other operations and resources
     * @param null $operationName
     * @return mixed|string
     */
    public function identifier($operationName = null)
    {

        if (!isset($this->_identifiers[$operationName])) {

            $parts = [
                $this->namespace,
                $this->name,
            ];

            $this->_identifiers[$operationName] = implode('.', $parts);

        }

        return $this->_identifiers[$operationName];
    }

}
