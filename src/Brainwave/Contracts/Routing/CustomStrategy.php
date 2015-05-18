<?php

namespace Brainwave\Contracts\Routing;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.9.8-dev
 */

/**
 * CustomStrategy.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
interface CustomStrategy
{
    /**
     * Dispatch the controller, the return value of this method will bubble out and be
     * returned by \Orno\Route\Dispatcher::dispatch, it does not require a response, however,
     * beware that there is no output buffering by default in the router.
     *
     * $controller can be one of three types but based on the type you can infer what the
     * controller actually is:
     *     - string   (controller is a named function)
     *     - array    (controller is a class method [0 => ClassName, 1 => MethodName])
     *     - \Closure (controller is an anonymous function)
     *
     * @param string|array|\Closure $controller
     * @param array                 $vars       - named wildcard segments of the matched route
     *
     * @return mixed
     */
    public function dispatch($controller, array $vars);
}
