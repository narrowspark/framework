<?php

namespace Brainwave\Contracts\Queue;

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
 * @version     0.9.7-dev
 */

/**
 * Factory.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.7-dev
 */
interface Factory
{
    /**
     * Resolve a queue connection instance.
     *
     * @param string $name
     *
     * @return \Brainwave\Contracts\Queue\Queue
     */
    public function connection($name = null);
}
