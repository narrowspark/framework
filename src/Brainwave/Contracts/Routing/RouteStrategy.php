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
 * @version     0.10.0-dev
 */

/**
 * CustomStrategy.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
interface RouteStrategy
{
    /**
     * Types of route strategies.
     */
    const REQUEST_RESPONSE_STRATEGY = 0;
    const RESTFUL_STRATEGY = 1;
    const URI_STRATEGY = 2;

    /**
     * Tells the implementor which strategy to use, this should override any higher
     * level setting of strategies, such as on specific routes.
     *
     * @param int $strategy
     */
    public function setStrategy($strategy);

    /**
     * Gets global strategy.
     *
     * @return int
     */
    public function getStrategy();
}
