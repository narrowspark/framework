<?php

namespace Brainwave\Loop;

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

use Brainwave\Contracts\Loop\Loop as LoopContract;
use Brainwave\Support\Manager;

/**
 * LoopManager.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
class LoopManager extends Manager implements LoopContract
{
    /**
     * Set the default cache driver name.
     *
     * @param string $name
     */
    public function setDefaultDriver($name) {

    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver() {

    }
}
