<?php

namespace Brainwave\Contracts\Exception;

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
 * Adapter.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
interface Adapter
{
    /**
     * Display the given exception to the user.
     *
     * @param \Exception $exception
     * @param int        $code
     */
    public function display(\Exception $exception, $code);
}
