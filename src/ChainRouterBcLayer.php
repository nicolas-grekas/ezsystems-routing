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

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

$refl = new \ReflectionClass(UrlGeneratorInterface::class);
$generateMethod = $refl->getMethod('generate');
$methodParameters = $generateMethod->getParameters();
/** @var \ReflectionParameter $nameParameter */
$nameParameter = array_shift($methodParameters);
if ($nameParameter && $nameParameter->hasType() && 'string' === $nameParameter->getType()) {
    /**
     * @internal
     */
    class ChainRouterBcLayer extends ChainRouterBaseBcLayer
    {
        /**
         * Loops through all registered routers and returns a router if one is found.
         * It will always return the first route generated.
         */
        public function generate(string $name, $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
        {
            return $this->doGenerate($name, $parameters, $absolute);
        }
    }
} else {
    /**
     * @internal
     */
    class ChainRouterBcLayer extends ChainRouterBaseBcLayer
    {
        /**
         * Loops through all registered routers and returns a router if one is found.
         * It will always return the first route generated.
         */
        public function generate($name, $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
        {
            if (!is_string($name)) {
                @trigger_error(sprintf('Passing an object as the route name is deprecated in symfony-cmf/Routing v2.2 and will not work in Symfony 5.0. Pass an empty route name and the object as "%s" parameter in the parameters array.', RouteObjectInterface::ROUTE_OBJECT), E_USER_DEPRECATED);

                $parameters[RouteObjectInterface::ROUTE_OBJECT] = $name;
                $name = '';
            }

            return $this->doGenerate($name, $parameters, $absolute);
        }
    }
}
