<?php

namespace AmyBoyd\SymfonyStuff\RouterValidation;

use Symfony\Component\Routing\Route;

final class ProblematicRoute
{
    private $name;

    private $route;

    private $exception;

    public function __construct($name, Route $route, \Exception $exception)
    {
        $this->name = $name;
        $this->route = $route;
        $this->exception = $exception;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getExceptionMessage()
    {
        return $this->exception->getMessage();
    }
}
