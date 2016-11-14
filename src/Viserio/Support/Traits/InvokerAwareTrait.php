<?php
declare(strict_types=1);
namespace Viserio\Support\Traits;

use Interop\Container\ContainerInterface;
use Viserio\Support\Invoker;

trait InvokerAwareTrait
{
    /**
     * The invoker instance.
     *
     * @var \Viserio\Support\Invoker
     */
    protected $invoker;

    /**
     * Set a Invoker instance.
     *
     * @param \Viserio\Support\Invoker $invoker
     *
     * @return $this
     */
    public function setInvoker(Invoker $invoker)
    {
        $this->invoker = $invoker;

        return $this;
    }

    /**
     * Get the container instance.
     *
     * @throws \RuntimeException
     *
     * @return \Interop\Container\ContainerInterface
     */
    abstract public function getContainer(): ContainerInterface;

    /**
     * Get configured invoker.
     *
     * @return \Viserio\Support\Invoker
     */
    protected function getInvoker(): Invoker
    {
        if ($this->invoker === null) {
            $this->invoker = new Invoker();

            if ($this->container !== null) {
                $this->invoker->setContainer($this->getContainer())
                    ->injectByTypeHint(true)
                    ->injectByParameterName(true);
            }
        }

        return $this->invoker;
    }
}
