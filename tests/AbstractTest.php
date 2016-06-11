<?php

namespace AmyBoyd\SymfonyStuff\RouterValidation\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Tester\CommandTester as ConsoleCommandTester;
use TestAppKernel;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{
    protected $kernel;

    protected $container;

    protected function setUp()
    {
        parent::setUp();

        $kernel = new TestAppKernel('test', false);
        $kernel->boot();
        $this->kernel = $kernel;
        $this->container = $kernel->getContainer();
    }

    protected function createCommandTester(ConsoleCommand $command, array $params = [])
    {
        $application = new ConsoleApplication($this->kernel);
        $application->add($command);

        $commandTester = new ConsoleCommandTester($command);
        $commandTester->execute($params);

        return $commandTester;
    }
}
