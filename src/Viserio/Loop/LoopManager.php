<?php
namespace Viserio\Loop;

use Viserio\Support\Manager;

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
