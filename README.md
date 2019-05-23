# Resources for Laravel

## Install

Via Composer

``` bash
$ composer require morningtrain/laravel-resources
$ php artisan vendor:publish --provider="MorningTrain\Laravel\Resources\LaravelResourcesServiceProvider"
```

## Configuration
Register your resources in `config/resources.php`:

The first level key corresponds to namespace, and value is an array of your resource classes.
You can give resources custom names with array keys, and nest arrays of resources. Just make sure all items have unique keys.

If no key is provided for a resource, the `Str::snake(class_basename($resource))` is used.
This means if you have a class called `User` and another resource with the key `"user"` in the same namespace, they will collide.

Example:
``` php
'api' => [
    \App\Resources\Api\User::class,
    'custom_user' => \App\Resources\Api\User::class,
    
    'nested' => [
        \App\Resources\Api\User::class,
        'custom_user' => \App\Resources\Api\User::class,
        
        'deep_nested' => [
            \App\Resources\Api\User::class,
            'custom_user' => \App\Resources\Api\User::class,
        ],
    
    /*
     * These two will not work together - non-uniqu resource name:
     *  \App\Resources\Api\MyResource::class,
     *  'my_resource' => \App\Resources\Api\User::class,
     */
],
```


## Usage
TODO

## Credits

- [Morning Train](https://morningtrain.dk/)

