<?php
namespace Viserio\Events\Interfaces;

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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * EventsAwareInterface.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
interface EventsAwareInterface
{
    /**
     * [setEventDispatcher description].
     *
     * @param EventDispatcherInterface $logger [description]
     */
    public function setEventDispatcher(EventDispatcherInterface $logger);
}
