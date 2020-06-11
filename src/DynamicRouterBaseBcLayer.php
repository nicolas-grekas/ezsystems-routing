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

use Symfony\Cmf\Component\Routing\Event\Events;
use Symfony\Cmf\Component\Routing\Event\RouterGenerateEvent;
use Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @internal
 */
abstract class DynamicRouterBaseBcLayer
{
    protected function doGenerate($name, $parameters, $referenceType)
    {
        if ($this->eventDispatcher) {
            $routeParam = $name;
            if (array_key_exists(RouteObjectInterface::ROUTE_OBJECT, $parameters) && is_object($parameters[RouteObjectInterface::ROUTE_OBJECT])) {
                $routeParam = $parameters[RouteObjectInterface::ROUTE_OBJECT];
            }

            $event = new RouterGenerateEvent($routeParam, $parameters, $referenceType);
            $this->doDispatch(Events::PRE_DYNAMIC_GENERATE, $event);

            $name = $event->getRoute();
            $parameters = $event->getParameters();
            $referenceType = $event->getReferenceType();
        }

        return $this->getGenerator()->generate($name, $parameters, $referenceType);
    }

    protected function doDispatch($eventName, $event)
    {
        // LegacyEventDispatcherProxy exists in Symfony >= 4.3
        if (class_exists(LegacyEventDispatcherProxy::class)) {
            // New Symfony 4.3 EventDispatcher signature
            $this->eventDispatcher->dispatch($event, $eventName);
        } else {
            // Old EventDispatcher signature
            $this->eventDispatcher->dispatch($eventName, $event);
        }
    }
}
