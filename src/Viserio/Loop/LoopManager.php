<?php
namespace Viserio\Loop;

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

use Viserio\Contracts\Loop\Loop as LoopContract;
use Viserio\Loop\Adapters\EventLoop;
use Viserio\Loop\Adapters\LibeventLoop;
use Viserio\Loop\Adapters\SelectLoop;
use Viserio\Support\Manager;

/**
 * LoopManager.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0
 */
class LoopManager extends Manager
{
    /**
     * Set the default cache driver name.
     *
     * @param string $name
     */
    public function setDefaultDriver($name)
    {
        $this->config->bind('loop::driver', $name);
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
<<<<<<< HEAD:src/Brainwave/Loop/LoopManager.php
        return $this->config->get('loop::driver', 'Brainwave\\Loop\\Adapters\\SelectLoop');
=======
        return $this->config->get('loop::driver', 'Viserio\\Loop\\Adapters\\SelectLoop');
>>>>>>> develop:src/Viserio/Loop/LoopManager.php
    }
}
