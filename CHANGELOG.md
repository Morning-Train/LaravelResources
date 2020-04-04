# Release Notes for 4.x

## [4.0.0 (2020-xx-xx)](https://github.com/Morning-Train/LaravelResources/compare/3.0.0...4.0.0)

- ResourceController will no longer provide func_get_args to execute method on operation (which no longer accepts any)
- Route actions `resource` and `operation` renamed to `resourceName` and `operationName`. 
  This and a similar update to the ResourceController is made to make more consistent naming across packages. 
- CrudResource has been renamed to EloquentResource
- The booting life-cycle of resources has been updated to only boot operations when needed. 
  Previously, all operations of a resource was boot if any operation from the resource was needed.
  This introduces a breaking change to how resources are configured.
- Identifiers in both operations and resources are only generated once and are then stored in the class.
- Removed support for the magic configuration methods in resources (i.e. `configureReadOperation`)
- Added new configuration methods for operations in resources, everything on an operation should now be configured here. (i.e. `indexOperation`)
- Removed public / is_public checks in Operation
- Removed status code helpers from Operation - They should be added in pipes where needed.
- Removed status message helpers from Operation - They should be added in pipes where needed.
- Removed name and identifier helpers from Operation - They are replaced with public class properties.
- Piping logic in Operation moved to HasPipes trait.
- Moved login from canExecute in Operation to IsPermitted pipe.
- Removed getMeta helpers from Operation - Meta will be added in pipes.
- Added Payload class which will be passed along the pipeline. 
  It handles making the response and is used to share data between pipes.
- Removed PipesPayload trait
- Chainable `after` and `before` methods added to HasPipes to make it possible to add extra pipes when configuring the Operation.
- Pipes are now on its own pipeable - which makes it possible to make "Pipe collections" which groups together multiple pipes.
- ToResponse and ToPayload pipes has been removed (Logic moved to Payload).
- Pipe method added to base Pipe class to make it easier to implement pipes without having to care about executing next pipe.
- After method added to Pipe (replaces logic from HasPipes) to execute logic after the entire pipeline.
- Pipe will proxy magic getters and setters to Payload to make it easier to interact with Payload data from pipes.
- IsPermitted pipe will now also apply permissions meta where needed. This removes the `SetPermissionsMeta` pipe.
- Split QueryToInstance pipe into QueryToModel and QueryToCollection pipes.
- The concept of setup pipes has been added together with the SetupFilters pipe. 
  It both sets filters on the payload and sets filters meta to response.
- A BladeView Pipe has been added with some logic from PageOperation
- A SetEnv pipe has been added to provide something to the context env.
- Eloquent folder under pipes added to contain any pipe related to Eloquent models. Most related pipes has been moved there.
- Pipes added to trigger method on model or all models in a collection (`TriggerOnModel` and `TriggerOnMOdelsInCollection`)
- QueryToCount pipe added, to return a count for the Query.
- FetchModel pipe collection added to perform the typical logic of querying an eloquent model and getting an instance.
- Most default pipes has been removed from EloquentOperation - Each operation will have to implement its own pipes.
- Crud operations has been moved to the Eloquent namespace.
- Eloquent operations has been updated to work with new Eloquent pipes.
- PageOperation has been renamed to Page and is moved to the Operations/Pages folder.
- Page and React operation has been updated to use pipes
- Removed support for the genericGetSet method.

## [3.0.0 (2020-03-04)](https://github.com/Morning-Train/LaravelResources/compare/2.11.0...3.0.0)

- Now only supports Laravel 7.0 and requires PHP 7.2.5 or higher
- Added laravel/ui as a dependency

## [2.11.0 (2020-03-04)](https://github.com/Morning-Train/LaravelResources/compare/2.10.0...2.11.0)

- Added initial support for Laravel 7.0

## [2.10.0 (2020-03-01)](https://github.com/Morning-Train/LaravelResources/compare/2.9.2...2.10.0)

- Operations will no longer implement its own identifier method but will 
rely on the resource identifier method to generate its own identifier. 
The resource identifier method has been updated to accept an optional operationName.

## [2.9.2 (2020-03-01)](https://github.com/Morning-Train/LaravelResources/compare/2.9.1...2.9.2)

- Bugfix: In canExecute method of operation, added is_public check to make open unrestricted operations.

## [2.9.1 (2020-03-01)](https://github.com/Morning-Train/LaravelResources/compare/2.9.0...2.9.1)

- Bugfix: Added missing return in public method of operation to make it chainable.

## [2.9.0 (2020-03-01)](https://github.com/Morning-Train/LaravelResources/compare/2.8.0...2.9.0)

- Added initial code for making an operation public. Public configuration method added 
as well as an is_public check when setting up the routes.

## [2.8.0 (2020-01-21)](https://github.com/Morning-Train/LaravelResources/compare/2.7.1...2.8.0)

- Timestamp meta plugin added to eloquent operation afterPipes. It will add a timestamp to response meta.

## [2.7.1 (2019-12-17)](https://github.com/Morning-Train/LaravelResources/compare/2.7.0...2.7.1)

- Bugfix: Added missing use for options variable in routes method of resource.

## [2.7.0 (2019-12-17)](https://github.com/Morning-Train/LaravelResources/compare/2.6.4...2.7.0)

- Initial setup for providing options when defining routes for resource. 
- Added option to override guard of resource routes by providing a `guard` option.

## [2.6.4 (2019-11-29)](https://github.com/Morning-Train/LaravelResources/compare/2.6.3...2.6.4)

- It is now possible to configure a forceRedirect ENV property on a page operation. 
  It will be provided in page environment (sent to frontend).

## [2.6.3 (2019-11-29)](https://github.com/Morning-Train/LaravelResources/compare/2.6.2...2.6.3)

- Added success helper to the Respondable trait. It will throw a HttpException with status code 200.
- ForgotPassword operation updated to use the Respondable trait and calls the success respondable helper.

## [2.6.2 (2019-11-27)](https://github.com/Morning-Train/LaravelResources/compare/2.6.1...2.6.2)

- Bugfix: Updated TransformToView pipe to correctly find Str Laravel helper.

## [2.6.1 (2019-11-26)](https://github.com/Morning-Train/LaravelResources/compare/2.6.0...2.6.1)

- Bugfix: Fixed namespace of MassAction pipe.

## [2.6.0 (2019-11-26)](https://github.com/Morning-Train/LaravelResources/compare/2.5.2...2.6.0)

- Added initial code for MassAction pipe.
- HasFilters trait will merge filters. Second parameter to the `filters` method added to turn off merging, 
  the default behaviour is that it will merge filters.
- Added KeyBy pipe to key a collection by the model key name.

## [2.5.2 (2019-11-20)](https://github.com/Morning-Train/LaravelResources/compare/2.5.1...2.5.2)

- Bugfix: EloquentOperation will check if route actually exists before using it to get the keyValue for QueryToInstance.

## [2.5.1 (2019-11-19)](https://github.com/Morning-Train/LaravelResources/compare/2.5.0...2.5.1)

- Bugfix: Fixed issue where closure triggers were not being called correctly in the Action operation.

## [2.5.0 (2019-11-14)](https://github.com/Morning-Train/LaravelResources/compare/2.4.3...2.5.0)

- Added HasRules pipe to configure a pipe with validation rules.
- Added ValidatesField pipe
- Modified Validates pipe to only validate using rules
- Store operation updated to use ValidatesFields instead of the Validates pipe.

## [2.4.3 (2019-11-14)](https://github.com/Morning-Train/LaravelResources/compare/2.4.2...2.4.3)

- In EloquentOperation, only apply model name as route parameter if model is set

## [2.4.2 (2019-11-14)](https://github.com/Morning-Train/LaravelResources/compare/2.4.1...2.4.2)

- Bugfix: Updated ToPayload with extra validation to catch some malformed/missing responses.

## [2.4.1 (2019-11-14)](https://github.com/Morning-Train/LaravelResources/compare/2.4.0...2.4.1)

- Added QueryModel to Count operation
- In toPayload pipe, pass along data if already an array

## [2.4.0 (2019-11-07)](https://github.com/Morning-Train/LaravelResources/compare/2.3.2...2.4.0)

- `morningtrain/laravel-context` dependency updated from version 1 to 2. It has an updated Context::env interface.
- Updated PageOperation to use `Context::env` instead of `Context::localization`.
- Updated ResourceRepository to use `Context::env` instead of `Context::localization`.

## [2.3.2 (2019-11-03)](https://github.com/Morning-Train/LaravelResources/compare/2.3.1...2.3.2)

- Moved set message logic from store to pipe
- Action operation updated to use pipes

## [2.3.1 (2019-11-03)](https://github.com/Morning-Train/LaravelResources/compare/2.3.0...2.3.1)

- Cleanup
- Moved logic for setting meta to pipes from EloquentOperation.

## [2.3.0 (2019-11-03)](https://github.com/Morning-Train/LaravelResources/compare/2.2.0...2.3.0)

- Cleanup
- prepare and initialPipes removed
- UnauthorizedException now returns a 403 status code
- Updated IsPermitted pipe to use AccessDeniedHttpException
- Added Respondable trait to ease sending error messages to user
- In store operation, moved main logic to UpdateModel pipe
- Added HasModel trait
- Updated Store operation to use EnsureModelInstance pipe
- Added HasFilters trait
- Fix to EnsureModelInstance - it mistakenly returned
- Eloquent operation updated to use HasModel and HasFilters
- Do not handle empty model case in QueryToInstance
- Moved Validates pipe to Store operation
- Extracted ToPayload logic from ToResponse pipe to prepare for meta pipes
- Removed deprecated restrict method
- Removed restricted prop from CrudResource
- Logic for adding meta to payload moved to Pipe
- EnsureModelInstance pipe will also return new Instance if model was not supplied











