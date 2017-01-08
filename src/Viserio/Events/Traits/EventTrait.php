<?php
declare(strict_types=1);
namespace Viserio\Events\Traits;

trait EventTrait
{
    /**
     * Event name.
     *
     * @var string
     */
    protected $name;

    /**
     * Event target/context an object OR static class name (string).
     *
     * @var object|string|null
     */
    protected $target;

    /**
     * Event parameters.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Stop propagation.
     *
     * @var bool
     */
    protected $propagationStopped = false;

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * {@inheritdoc}
     */
    public function getParams(): array
    {
        return $this->parameters;
    }

    /**
     * Stops the propagation of the event to further event listeners.
     *
     * If multiple event listeners are connected to the same event, no
     * further event listener will be triggered once any trigger calls
     * stopPropagation().
     */
    public function stopPropagation()
    {
        $this->propagationStopped = true;
    }

    /**
     * {@inheritdoc}
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}
