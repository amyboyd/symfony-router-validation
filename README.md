Symfony router validation
=========================

This library provides a service and command for checking if the routes in your router configuration
are valid.

Install
-------

Add the package to your dependencies:

```
composer require amyboyd/symfony-router-validation
```

Add these lines to your services configuration (e.g. in `services.yml` or `config.yml`):

```yml
services:
    amyboyd.command.router_validate_command:
        class: AmyBoyd\SymfonyStuff\RouterValidation\RouterValidationCommand
        tags:
            -  { name: console.command }

    amyboyd.router_validation:
        class: AmyBoyd\SymfonyStuff\RouterValidation\RouterValidationService
        arguments:
            controllerNameParser: '@controller_name_converter'
            container: '@service_container'
```

To check your routes are valid from the command line:

* In Symfony 2.*, run `app/console router:validate`
* In Symfony 3.*, run `bin/console router:validate`

Contribute
----------

To set up for local development:

```
git clone git@github.com:amyboyd/symfony-router-validation.git
cd symfony-router-validation
bin/composer-install
```

To verify your installation is working correctly, run the tests:

```
bin/test
bin/run-validate-command
```

The tests are run against multiple versions of Symfony (currently 2.5, 2.8, 3.0 and 3.1).

Sample output
-------------

```
3 valid routes
6 invalid routes
app_invalid_controller_because_bundle_does_not_exist - Bundle "InvalidBundle" (from the _controller value "InvalidBundle:Default:index") does not exist, or is it not enabled
app_invalid_controller_because_of_trailing_action - Method "indexActionAction" does not exist in class "AppBundle\Controller\DefaultController".
app_invalid_controller_because_class_does_not_exist - The _controller value "AppBundle:InvalidClass:index" maps to a "AppBundle\Controller\InvalidClassController" class, but this class was not found. Create this class or check the spelling of the class and its namespace.
app_invalid_service_because_service_doesnt_exist - You have requested a non-existent service "app.invalid_controller_service". Did you mean this: "app.controller_service"?
app_invalid_service_because_method_doesnt_exist - Method "invalidMethodAction" does not exist in class "AppBundle\Controller\DefaultController".
app_invalid_missing_controller - _controller property is not set.
2 routes are in a format not supported by this command  - Please report the details at https://github.com/amyboyd/symfony-router-validation/issues
app_invalid_route_format - Route format "blabla!£%" is not supported by this tool
app_invalid_route_format_2 - Route format "AppBundleDefault@" is not supported by this tool
```
