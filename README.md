# Resources and Operations for Laravel
Operations are a set of actions on the server that handles all logic from setting up routes to handling client requests.

A simple operation could be an index operation for getting all users and returning them to the client. 

Another use-case could be triggering an action on an Eloquent model. 
When doing this, one would always have to validate user input, fetch the model, 
execute the method and then perhaps return something meaningful to the user.

This is similar to a simple operation where we need to return a single instance of a model to the user 
(a traditional read call) - but it contains some additional logic to handle method execution.

A delete operation is similar, in that it first fetches the model and the trigger the delete method on it.

## Install

Via Composer

``` bash
$ composer require morningtrain/laravel-resources
$ php artisan vendor:publish --provider="MorningTrain\Laravel\Resources\LaravelResourcesServiceProvider"
```

## Configuration
Register your resources and operations in `config/resources.php`:

The first level key corresponds to namespace, and value is an array of your resource classes.
You can give resources custom names with array keys, and nest arrays of resources. Just make sure all items have unique keys.

If no key is provided for a resource, the `Str::snake(class_basename($resource))` is used.
This means if you have a class called `User` and another resource with the key `"user"` in the same namespace, they will collide.

Example:
``` php
'api' => [
    \App\Operations\Api\User::class,
    'custom_user' => \App\Operations\Api\User::class,
    
    'nested' => [
        \App\Operations\Api\User::class,
        'custom_user' => \App\Operations\Api\User::class,
        
        'deep_nested' => [
            \App\Operations\Api\User::class,
            'custom_user' => \App\Operations\Api\User::class,
        ],
    
    /*
     * These two will not work together - non-unique resource name:
     *  \App\Operations\Api\MyResource::class,
     *  'my_resource' => \App\Operations\Api\User::class,
     */
],
```


## Examples


### Index Operation
The purpose of the `Index` operation is to retrieve a list of model entries and return the list as JSON.

This code example is taken from the package and will illustrate how the Index operation is implemented.

```php
<?php

namespace MorningTrain\Laravel\Resources\Operations\Eloquent;

use MorningTrain\Laravel\Resources\Support\Contracts\EloquentOperation;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\QueryToCollection;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\QueryModel;
use MorningTrain\Laravel\Resources\Support\Pipes\TransformToView;

class Index extends EloquentOperation
{

    const ROUTE_METHOD = 'get';

    protected function beforePipes()
    {
        return [

            /**
             * Takes filters and model name as parameters and returns a new query object
             */
            QueryModel::create()->model($this->model)->filters($this->getCachedFilters()),

            /**
             * Trigger `get` on the query and returns the resulting collection
             */
            QueryToCollection::create(),

            /**
             * Transform the collection by applying any appends to each model in the entry
             */
            TransformToView::create()->appends($this->appends, $this->overwrite_appends),
        ];
    }

}

```

On a project level, the Index class can be either implemented in a Resource or as a single Operation.

The following example is an `EloquentResource` implementing a single `Index` operation.
The operation is already added in the base class, so it is not really neccesary to have the static `$operations` property.

In our example, users are returned with a pagination filter applied.

```php
<?php

namespace App\Http\Operations\Api;

use MorningTrain\Laravel\Resources\Support\Contracts\EloquentResource;
use MorningTrain\Laravel\Resources\Operations\Eloquent\Index;
use MorningTrain\Laravel\Filters\Filter;
use App\Models\User as Model;

class Users extends EloquentResource
{

    protected $model = Model::class;
    
    public static $operations = [
        Index::class,
    ];

    public function getFilters(){
        return [
            Filter::paginate(),
        ];
    }

}

```

The following example is similar to the one above, but will have the operation as a single class.

```php
<?php

namespace App\Http\Operations\Api\Users;

use MorningTrain\Laravel\Resources\Operations\Eloquent\Index as Operation;
use MorningTrain\Laravel\Filters\Filter;
use App\Models\User as Model;

class IndexUsers extends Operation
{

    protected $model = Model::class;

    public function getFilters(){
        return [
            Filter::paginate(),
        ];
    }

}

```


## Design principles
Much of what we do with operations can be achieved using a traditional MVC approach. 
It is however easy for controller logic to degrade into duplicated and badly structured code.

Our goal when implementing *operations* in our setup is to gain a way to greatly increase code reuse.
When we have found a good solution for solving a specific task, why not reuse it for all similar tasks?

### Resources
What we call a *resource* in this context, is essentially a collection or group of operations. 
It is inside of a resource, that each and every operation is initialised and configured. 

### Configuration
Having operations being configurable means that we can build a multipurpose operation that is used in similar situations but with slightly different behaviour.

A common example could be that of a typical index operation to query an Eloquent model based on a set of filters and return a collection to the user.

In this case, our Index operation is configured to use a specific model class (as would be the case with all Eloquent operations) and an array of filters.

So far we have only been mentioning API operations, but an operation could also be a normal page request. 
In this case it would be configured to have a prettier route path and for instance to return a given view.
For most of our use cases, the page operation renders a view that outputs a desired React component.

It is a given that all operations should be configured to be protected by a permission layer in order to control access. 
Using Laravel, we are in the end utilizing the underlying policy and gate system to control access. 
It is spiced up with an extra permission laravel package to create the needed models and database structures to support roles and permissions.

### Micro tasks using pipelines
With the release of version 2.x of our [Laravel package](https://packagist.org/packages/morningtrain/laravel-resources), 
the main logic of operations are split into multiple smaller tasks in order to allow for futher code reuse. 
This is done using the Laravel pipeline setup that are also used internally by Laravel for handling middlewares. 

Documentation for Pipelines in Laravel 5.7 can be found [here](https://laravel.com/api/5.7/Illuminate/Pipeline/Pipeline.html). 

This allows for a higher degree of customization between similar operations while keeping base logic decoupled.

Every minor task is called a *Pipe* to reflect it being a part of an operation pipeline.

Examples of pipes:

 - Validate: Validates the incoming http request using Laraval compliant validation rules
 - Filter query: It allows for a DB query builder instance to be filtered based on HTTP request variables.
 - Prepare response payload: The return value of the main operation logic is uniformly transformed into a payload JSON object. 
 
Having operations structured as a pipeline also keeps actual code/logic out of the operations themselves. What is left is more or less a configurable class.
To make changes and adaptations to how an operation works, one would only have to copy/extend the operations and changes to the pipeline.
In most cases, no addition code is required.  

## Credits
This package is developed and actively maintained by [Morningtrain](https://morningtrain.dk).

<!-- language: lang-none -->
     _- _ -__ - -- _ _ - --- __ ----- _ --_  
    (         Morningtrain, Denmark         )
     `---__- --__ _ --- _ -- ___ - - _ --_ ´ 
         o                                   
        .  ____                              
      _||__|  |  ______   ______   ______ 
     (        | |      | |      | |      |
     /-()---() ~ ()--() ~ ()--() ~ ()--() 
    --------------------------------------

