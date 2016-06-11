<?php

namespace AmyBoyd\SymfonyStuff\RouterValidation;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class RouterValidationService
{
    private $controllerNameParser;

    private $container;

    public function __construct(ControllerNameParser $controllerNameParser, ContainerInterface $container)
    {
        $this->controllerNameParser = $controllerNameParser;
        $this->container = $container;
    }

    public function validateRoutes(RouteCollection $routes)
    {
        $validCount = 0;
        $invalidRoutes = [];
        $unsupportedRoutes = [];

        foreach ($routes as $routeName => $route) {
            try {
                $this->validateRoute($route);
            } catch (InvalidRouteException $e) {
                $invalidRoutes[] = new ProblematicRoute($routeName, $route, $e);
                continue;
            } catch (UnsupportedRouteFormatException $e) {
                $unsupportedRoutes[] = new ProblematicRoute($routeName, $route, $e);
                continue;
            }

            $validCount += 1;
        }

        return [
            'validCount' => $validCount,
            'invalidRoutes' => $invalidRoutes,
            'unsupportedRoutes' => $unsupportedRoutes,
        ];
    }

    /**
     * @param  string $controller
     *
     * @throws InvalidRouteException
     * @throws UnsupportedRouteFormatException
     */
    private function validateRoute(Route $route)
    {
        if (!isset($route->getDefaults()['_controller'])) {
            throw new InvalidRouteException('_controller property is not set.');
        }

        $controller = $route->getDefaults()['_controller'];

        if (substr_count($controller, ':') === 2 && strpos($controller, '::') !== false) {
            // Controller was in the `Bundle:Controller:Action` notation.
            // It will have been parsed to `Org\Bundle\XBundle\Controller\YController::somethingAction`.
            list($className, $methodName) = explode('::', $controller, 2);

            if (!class_exists($className)) {
                throw new InvalidRouteException(sprintf('Class "%s" does not exist.', $className));
            }

            $this->validatePublicMethodExistsInClass($className, $methodName);
        } elseif (substr_count($controller, ':') === 2 && strpos($controller, '::') === false) {
            // Controller is in the `Bundle:Controller:Action` notation.
            try {
                $controller = $this->controllerNameParser->parse($controller);
            } catch (\InvalidArgumentException $e) {
                // The exception message if the bundle does not exist has changed after Symfony 2.5.
                // Use our own message for consistency.
                $isBundleDoesNotExistException = strpos($e->getMessage(), 'does not exist or it is not enabled') !== false
                    || strpos($e->getMessage(), ' does not exist or is not enabled in your kernel') !== false;
                if ($isBundleDoesNotExistException) {
                    $bundle = substr($controller, 0, strpos($controller, ':'));
                    $message = sprintf('Bundle "%s" (from the _controller value "%s") does not exist, or is it not enabled', $bundle, $controller);
                    throw new InvalidRouteException($message, null, $e);
                }

                throw new InvalidRouteException($e->getMessage(), null, $e);
            }

            // Controller will now have been parsed to
            // `Org\Bundle\XBundle\Controller\YController::somethingAction`.
            list($className, $methodName) = explode('::', $controller, 2);

            $this->validatePublicMethodExistsInClass($className, $methodName);
        } elseif (substr_count($controller, ':') === 1) {
            // Controller is in the `service:method` notation.
            list($service, $methodName) = explode(':', $controller, 2);

            try {
                $serviceInstance = $this->container->get($service);
            } catch (ServiceNotFoundException $e) {
                throw new InvalidRouteException($e->getMessage(), null, $e);
            }

            $this->validatePublicMethodExistsInClass(get_class($serviceInstance), $methodName);
        } else {
            throw new UnsupportedRouteFormatException(sprintf('Route format "%s" is not supported by this tool', $controller));
        }
    }

    /**
     * @param  string $className
     * @param  string $methodName
     *
     * @throws InvalidRouteException
     */
    private function validatePublicMethodExistsInClass($className, $methodName)
    {
        $class = new \ReflectionClass($className);

        try {
            $method = $class->getMethod($methodName);
        } catch (\ReflectionException $e) {
            throw new InvalidRouteException(sprintf('Method "%s" does not exist in class "%s".', $methodName, $className));
        }

        if (!$method->isPublic()) {
            throw new InvalidRouteException(sprintf('Method "%s" is not public in class "%s".', $methodName, $className));
        }
    }
}
