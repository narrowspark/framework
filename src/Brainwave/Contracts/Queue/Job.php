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
 * Job.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.7-dev
 */
interface Job
{
    const STATUS_UNKNOWN = 0;

    const STATUS_REQUEUE = 1;

    const STATUS_FAILED = 2;

    const STATUS_DELETE = 3;
}
