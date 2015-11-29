<?php
namespace Viserio\Contracts\Application;

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
 * @version     0.10.0
 */

use Viserio\Contracts\Container\ServiceProvider as ContainerServiceProvider;

/**
 * ServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
interface ServiceProvider extends ContainerServiceProvider
{
    /**
     * Subscribe events.
     *
     * @param array|null $commands
     */
    public function commands(array $commands = null);
}
