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
 * Consumer.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.7-dev
 */
interface Consumer
{
    /**
     * @param $event
     */
    public function consume($event);
}
