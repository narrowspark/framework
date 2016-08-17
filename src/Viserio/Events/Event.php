<?php
declare(strict_types=1);
namespace Viserio\Events;

use InvalidArgumentException;
use Viserio\Contracts\Events\Event as EventContract;
use Viserio\Events\Traits\ValidateNameTrait;

class Event implements EventContract
{
    use ValidateNameTrait;

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
    protected $parameters;

    /**
     * Stop propagation.
     *
     * @var bool
     */
    protected $stopped = false;

    /**
     * Create a new event instance.
     *
     * @param string             $eventName  event name
     * @param string|object|null $target     event context, object or classname
     * @param array              $parameters event parameters
     *
     * @throws InvalidArgumentException if event name is invalid
     */
    public function __construct(string $eventName, $target = null, array $parameters = [])
    {
        $this->setName($eventName);
        $this->setTarget($target);
        $this->setParams($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        if (! is_string($name) || empty($name)) {
            throw new InvalidArgumentException(sprintf('Event name "%s" is not valid', $name));
        }

        $this->name = $this->validateEventName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setTarget($target)
    {
        $this->target = $target;
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
    public function setParams(array $params)
    {
        $this->parameters = $params;
    }

    /**
     * {@inheritdoc}
     */
    public function getParams()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getParam($name)
    {
        return $this->parameters[(string) $name] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function stopPropagation($flag)
    {
        $this->stopped = (bool) $flag;
    }

    /**
     * {@inheritdoc}
     */
    public function isPropagationStopped()
    {
        return $this->stopped;
    }
}
