# Release Notes

## [4.0.0 (2020-04-20)](https://github.com/Morning-Train/LaravelResources/compare/3.0.0...4.0.0)

- Added support for __request_uuid mirroring to help protect against racing requests
- Message pipe added with trans and transChoice helpers
- SetModelSuccessMessage pipe moved to Messages pipe namespace and renamed to ModelUpdatedMessage
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

## [2.2.0 (2019-11-03)](https://github.com/Morning-Train/LaravelResources/compare/2.1.0...2.2.0)

- Basic logic from EloquentOperation moved to pipes (`QueryToInstance` and `TransformToView`)

## [2.1.0 (2019-11-03)](https://github.com/Morning-Train/LaravelResources/compare/2.0.1...2.1.0)

- StaticCreate trait has been added to Pipe to make them easier to create and configure
- Method to set operation on pipe is changed to not being static.
- In operation, handle is now called in pipe closure
- Validation of fields is moved to pipe
- Permission check moved from Resource controller to operation
- Prepare methods moved to pipeline and initialPipes method added
- Refactored route generation in eloquent and base operation to allow for dynamic parameters
- In eloquent operation - more directly pull key value from route parameter
- QueryModel pipe added to replace query logic in EloquentOperation

## [2.0.1 (2019-11-02)](https://github.com/Morning-Train/LaravelResources/compare/2.0.0...2.0.1)

- Pipes refactored in operation to make them more extendable. Added methods for 
  `beforePipes`, `afterPipes`, `responsePipes` and a `buildPipes` method to merge the different pipes together.

## [2.0.0 (2019-11-02)](https://github.com/Morning-Train/LaravelResources/compare/1.4.0...2.0.0)

- Initial pipeline setup. Operation has pipelines
- ResourceController updated its exectute part to match the pipeline setup in operations.
- Added base Pipe class.

## [1.4.0 (2019-11-02)](https://github.com/Morning-Train/LaravelResources/compare/1.3.6...1.4.0)

- Macroable trait added to operations
- When generating operation route - rely on isRestricted macro or method to check if auth middleware should be applied.
- Permission related methods moved from ResourceRepository to morningtrain/laravel-permissions package

## [1.3.6 (2019-10-31)](https://github.com/Morning-Train/LaravelResources/compare/1.3.5...1.3.6)

- Updated how flash messages are handled (they are added to session) and used in PageOperation and VerifyEmail operation.

## [1.3.5 (2019-10-30)](https://github.com/Morning-Train/LaravelResources/compare/1.3.4...1.3.5)

- Added VerifyEmail operation using Laravel VerifiesEmails trait as base.
- Added ResendVerificationEmail operation using Laravel VerifiesEmails trait as base.

## [1.3.4 (2019-10-29)](https://github.com/Morning-Train/LaravelResources/compare/1.3.3...1.3.4)

- Responses in ForgotPassword and ResetPassword operations updated to use setMessage and setStatusCode helpers.

## [1.3.3 (2019-10-14)](https://github.com/Morning-Train/LaravelResources/compare/1.3.2...1.3.3)

- Bugfix: Reverted older commits 

## [1.3.2 (2019-10-14)](https://github.com/Morning-Train/LaravelResources/compare/1.3.1...1.3.2)

- Payload updated to check for universal Symfony response instead of checking on individual Laravel responses.

## [1.3.1 (2019-10-09)](https://github.com/Morning-Train/LaravelResources/compare/1.3.0...1.3.1)

- Operations are now considered restricted if they are defined in permissions config

## [1.3.0 (2019-10-04)](https://github.com/Morning-Train/LaravelResources/compare/1.2.28...1.3.0)

- Permission handling updated to use Gate::allows instead of custom logic

## [1.2.28 (2019-09-26)](https://github.com/Morning-Train/LaravelResources/compare/1.2.27...1.2.28)

- Support added for custom model permissions

## [1.2.27 (2019-09-19)](https://github.com/Morning-Train/LaravelResources/compare/1.2.26...1.2.27)

- Accept closure as Action trigger

## [1.2.26 (2019-09-18)](https://github.com/Morning-Train/LaravelResources/compare/1.2.25...1.2.26)

- Store operation gets a default success message

## [1.2.25 (2019-09-13)](https://github.com/Morning-Train/LaravelResources/compare/1.2.24...1.2.25)

- Added getOperationPolicyParameters to ResourceRepository - Return a list of operation/permission names 
  which have policy methods not requiring an instance of the Model.

## [1.2.24 (2019-09-04)](https://github.com/Morning-Train/LaravelResources/compare/1.2.23...1.2.24)

- Bugfix: Auth register operation uses overridden registered from trait instead of parent

## [1.2.23 (2019-09-04)](https://github.com/Morning-Train/LaravelResources/compare/1.2.22...1.2.23)

- Register operation updated to match Laravel 6.0.

## [1.2.22 (2019-09-04)](https://github.com/Morning-Train/LaravelResources/compare/1.2.21...1.2.22)

- Support Laravel 6 illuminate packages

## [1.2.21 (2019-08-23)](https://github.com/Morning-Train/LaravelResources/compare/1.2.20...1.2.21)

- In env for page operation, also export current resource namespace. It is used to assist frontend navigation.

## [1.2.20 (2019-08-23)](https://github.com/Morning-Train/LaravelResources/compare/1.2.19...1.2.20)

- Bugfix: successMessage and errorMessage are now chainable.

## [1.2.19 (2019-08-23)](https://github.com/Morning-Train/LaravelResources/compare/1.2.18...1.2.19)

- Default success and error message can be configured on operation.

## [1.2.18 (2019-08-20)](https://github.com/Morning-Train/LaravelResources/compare/1.2.17...1.2.18)

- In payload, factor in redirect and json response types

## [1.2.17 (2019-08-12)](https://github.com/Morning-Train/LaravelResources/compare/1.2.16...1.2.17)

- Status code and message in payload is now pulled from operation in default pathway

## [1.2.16 (2019-08-12)](https://github.com/Morning-Train/LaravelResources/compare/1.2.15...1.2.16)

- Bugfix: Parent is not set as a property on page operation.

## [1.2.15 (2019-07-09)](https://github.com/Morning-Train/LaravelResources/compare/1.2.14...1.2.15)

- Added helpers for setting parent in page operation.

## [1.2.14 (2019-07-09)](https://github.com/Morning-Train/LaravelResources/compare/1.2.13...1.2.14)

- Generic eloquent Count operation added.

## [1.2.13 (2019-06-27)](https://github.com/Morning-Train/LaravelResources/compare/1.2.12...1.2.13)

- Logic for exporting filter meta has been moved to filters from eloquent operation.

## [1.2.12 (2019-06-12)](https://github.com/Morning-Train/LaravelResources/compare/1.2.11...1.2.12)

- Added method to set appends on eloquent operation.

## [1.2.11 (2019-06-07)](https://github.com/Morning-Train/LaravelResources/compare/1.2.10...1.2.11)

- Only apply model class name to route path in eloquent operation if model is supplied

## [1.2.10 (2019-06-06)](https://github.com/Morning-Train/LaravelResources/compare/1.2.9...1.2.10)

- Added methods getModelClassName and getRoutePath to Eloquent operations. 
  They are added to export and used to generate routes in the frontend.

## [1.2.9 (2019-06-04)](https://github.com/Morning-Train/LaravelResources/compare/1.2.8...1.2.9)

- In EloquentOperation, model name is exported.

## [1.2.8 (2019-06-03)](https://github.com/Morning-Train/LaravelResources/compare/1.2.7...1.2.8)

- Use namespace when caching operations on resources.
- Identifier method has been added in resource class.

## [1.2.7 (2019-05-31)](https://github.com/Morning-Train/LaravelResources/compare/1.2.6...1.2.7)

- Page operation will export title to ENV exports.

## [1.2.6 (2019-05-31)](https://github.com/Morning-Train/LaravelResources/compare/1.2.5...1.2.6)

- React operation now adds namespace to component in ENV exports.

## [1.2.5 (2019-05-31)](https://github.com/Morning-Train/LaravelResources/compare/1.2.4...1.2.5)

- React operation will export component to ENV exports.
- Eloquent(crud) Action operation added.

## [1.2.4 (2019-05-30)](https://github.com/Morning-Train/LaravelResources/compare/1.2.3...1.2.4)

- Payload updated to not require eloquent instance in modelResponse method.

## [1.2.3 (2019-05-30)](https://github.com/Morning-Train/LaravelResources/compare/1.2.2...1.2.3)

- Bugfix: Added title property to PageOperation

## [1.2.2 (2019-05-30)](https://github.com/Morning-Train/LaravelResources/compare/1.2.1...1.2.2)

- Removed "resources" as a static part of the operation and resource identifiers. 

## [1.2.1 (2019-05-30)](https://github.com/Morning-Train/LaravelResources/compare/1.2.0...1.2.1)

- Page and React operations updated to provide title and page info to ENV exports.

## [1.2.0 (2019-05-23)](https://github.com/Morning-Train/LaravelResources/compare/1.1.10...1.2.0)

- Resource namespacing has been reworked.





