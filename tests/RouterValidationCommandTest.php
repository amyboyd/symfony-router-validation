<?php

namespace AmyBoyd\SymfonyStuff\RouterValidation\Tests;

use AmyBoyd\SymfonyStuff\RouterValidation\RouterValidationCommand;

class RouterValidationCommandTest extends AbstractTest
{
    public function testExecute()
    {
        $commandTester = parent::createCommandTester(new RouterValidationCommand(), []);
        $display = $commandTester->getDisplay();

        $expectedLines = [
            '3 valid routes',
            '6 invalid routes',
            'app_invalid_controller_because_bundle_does_not_exist - Bundle "InvalidBundle" (from the _controller value "InvalidBundle:Default:index") does not exist, or is it not enabled',
            'app_invalid_controller_because_of_trailing_action - Method "indexActionAction" does not exist in class "AppBundle\Controller\DefaultController"',
            'app_invalid_controller_because_class_does_not_exist', // The message varies between Symfony 2.5/3.1.
            'app_invalid_service_because_service_doesnt_exist - You have requested a non-existent service "app.invalid_controller_service". Did you mean this: "app.controller_service"?',
            'app_invalid_service_because_method_doesnt_exist - Method "invalidMethodAction" does not exist in class "AppBundle\Controller\DefaultController".',
            'app_invalid_missing_controller - _controller property is not set.',
            '2 routes are in a format not supported by this command',
            'app_invalid_route_format - Route format "blabla!£%" is not supported by this tool',
            'app_invalid_route_format_2 - Route format "AppBundleDefault@" is not supported by this tool',
        ];
        foreach ($expectedLines as $expectedLine) {
            $this->assertContains($expectedLine, $display);
        }

        $this->assertEquals(1, $commandTester->getStatusCode());
    }
}
