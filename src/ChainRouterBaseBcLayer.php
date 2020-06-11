<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing;

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @internal
 */
abstract class ChainRouterBaseBcLayer
{
    protected function doGenerate($name, $parameters, $absolute)
    {
        $debug = [];

        foreach ($this->all() as $router) {
            // if $router does not announce it is capable of handling
            // non-string routes and $name is not a string, continue
            if (array_key_exists(RouteObjectInterface::ROUTE_OBJECT, $parameters) && is_object($parameters[RouteObjectInterface::ROUTE_OBJECT]) && !$router instanceof VersatileGeneratorInterface) {
                continue;
            }

            $routeName = $name;
            if (array_key_exists(RouteObjectInterface::ROUTE_OBJECT, $parameters) && is_object($parameters[RouteObjectInterface::ROUTE_OBJECT])) {
                $routeName = $parameters[RouteObjectInterface::ROUTE_OBJECT];
            }

            // If $router is versatile and doesn't support this route name, continue
            if ($router instanceof VersatileGeneratorInterface && !$router->supports($routeName)) {
                continue;
            }

            try {
                return $router->generate($name, $parameters, $absolute);
            } catch (RouteNotFoundException $e) {
                $hint = $this->getErrorMessage($name, $router, $parameters);
                $debug[] = $hint;
                if ($this->logger) {
                    $this->logger->debug('Router '.get_class($router)." was unable to generate route. Reason: '$hint': ".$e->getMessage());
                }
            }
        }

        if ($debug) {
            $debug = array_unique($debug);
            $info = implode(', ', $debug);
        } else {
            $info = $this->getErrorMessage($name);
        }

        throw new RouteNotFoundException(sprintf('None of the chained routers were able to generate route: %s', $info));
    }

    private function getErrorMessage($name, $router = null, $parameters = null)
    {
        if ($router instanceof VersatileGeneratorInterface) {
            // the $parameters are not forced to be array, but versatile generator does typehint it
            if (!is_array($parameters)) {
                $parameters = [];
            }
            $displayName = $router->getRouteDebugMessage($name, $parameters);
        } elseif (is_object($name)) {
            $displayName = method_exists($name, '__toString')
                ? (string) $name
                : get_class($name)
            ;
        } else {
            $displayName = (string) $name;
        }

        return "Route '$displayName' not found";
    }
}
