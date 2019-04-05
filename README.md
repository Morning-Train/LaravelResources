# Resources for Laravel

## Install

Via Composer

``` bash
$ composer require morningtrain/laravel-resources
$ php artisan vendor:publish --provider="MorningTrain\Laravel\Resources\LaravelResourcesServiceProvider"
```

## Configuration
Register your resources in `config/resources.php`:

The key corresponds to namespace, value is array of your resource classes.

Example:
``` php
'api' => [
    \App\Resources\Api\User::class,
],
```


## Usage
TODO

## Credits

- [Morning Train](https://morningtrain.dk/)

