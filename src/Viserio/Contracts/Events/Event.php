<?php
declare(strict_types=1);
namespace Viserio\Contracts\Events;

interface Event
{
    /**
     * Get event name
     *
     * @return string
     */
    public function getName();

    /**
     * Get target/context from which event was triggered
     *
     * @return null|string|object
     */
    public function getTarget();

    /**
     * Get parameters passed to the event
     *
     * @return array
     */
    public function getParams();

    /**
     * Get a single parameter by name
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParam($name);

    /**
     * Set the event name
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Set the event target
     *
     * @param null|string|object $target
     */
    public function setTarget($target);

    /**
     * Set event parameters
     *
     * @param array $params
     */
    public function setParams(array $params);

    /**
     * Indicate whether or not to stop propagating this event
     *
     * @param bool $flag
     */
    public function stopPropagation($flag);

    /**
     * Has this event indicated event propagation should stop?
     *
     * @return bool
     */
    public function isPropagationStopped();
}
