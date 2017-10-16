<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Traits;

use Invoker\InvokerInterface;
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
     * Get configured invoker.
     *
     * @return \Invoker\InvokerInterface
     */
    protected function getInvoker(): InvokerInterface
    {
        if ($this->invoker === null) {
            $this->invoker = new Invoker();

            if ($this->container !== null) {
                $this->invoker->setContainer($this->container)
                    ->injectByTypeHint(true)
                    ->injectByParameterName(true);
            }
        }

        return $this->invoker;
    }
}
