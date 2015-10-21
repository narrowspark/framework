<?php

namespace Brainwave\Contracts\View;

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

use Brainwave\Contracts\Support\Renderable;

/**
 * View.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
interface View extends Renderable
{
    /**
     * Get the name of the view.
     *
     * @return string
     */
    public function name();

    /**
     * Add a piece of data to the view.
     *
     * @param string|array $key
     * @param mixed        $value
     *
     * @return $this
     */
    public function with($key, $value = null);
}
