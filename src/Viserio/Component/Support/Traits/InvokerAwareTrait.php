<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Traits;

use Invoker\InvokerInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Support\Invoker;

trait InvokerAwareTrait
{
    /**
     * The invoker instance.
     *
     * @var \Viserio\Component\Support\Invoker
     */
    protected $invoker;

    /**
     * Set a Invoker instance.
     *
     * @param \Invoker\InvokerInterface $invoker
     *
     * @return $this
     */
    public function setInvoker(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;

        return $this;
    }

    /**
     * Get the container instance.
     *
     * @throws \RuntimeException
     *
     * @return \Psr\Container\ContainerInterface
     */
    abstract public function getContainer(): ContainerInterface;

    /**
     * Get configured invoker.
     *
     * @return \Invoker\InvokerInterface
     */
    protected function getInvoker(): InvokerInterface
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
