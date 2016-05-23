<?php
namespace Viserio\Events;

use Interop\Container\ContainerInterface as ContainerContract;
use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Dispatcher implements EventDispatcherInterface
{
    /**
     * Event dispatcher.
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Container instance.
     *
     * @var \Interop\Container\ContainerInterface
     */
    protected $container;

    /**
     * Listener list.
     *
     * @var array
     */
    protected $listenerIds = [];

    /**
     * Constructor.
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param ContainerContract                                           $container
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, ContainerContract $container)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->container       = $container;
    }

    /**
     * Adds a service as event listener.
     *
     * @param string   $eventName Event for which the listener is added
     * @param string[] $callback  The service ID of the listener service & the method
     *                            name that has to be called
     * @param int      $priority  The higher this value, the earlier an event listener
     *                            will be triggered in the chain. Defaults to 0.
     */
    public function addListenerService(string $eventName, array $callback, int $priority = 0)
    {
        if (!is_array($callback) || 2 !== count($callback)) {
            throw new InvalidArgumentException('Expected an [service", "method"] argument');
        }

        $serviceId = $callback[0];
        $method = $callback[1];

        $closure = function (Event $events) use ($serviceId, $method) {
            call_user_func([$this->container->get($serviceId), $method], $events);
        };

        $this->listenerIds[$eventName][] = [$callback, $closure];
        $this->eventDispatcher->addListener($eventName, $closure, $priority);
    }

    /**
     * Remove listener.
     *
     * @param string   $eventName Event for which the listener is added
     * @param string[] $listener
     */
    public function removeListener(string $eventName, array $listener)
    {
        foreach ($this->listenerIds[$eventName] as $i => $parts) {
            list($callback, $closure) = $parts;

            if ($listener === $callback) {
                $listener = $closure;
                break;
            }
        }

        $this->eventDispatcher->removeListener($eventName, $listener);
    }

    /**
     * Adds a service as event subscriber.
     *
     * @param string $serviceId The service ID of the subscriber service
     * @param string $class     The service's class name
     */
    public function addSubscriberService(string $serviceId, string $class)
    {
        $this->checkForInterface($class);

        foreach ($class::getSubscribedEvents() as $eventName => $params) {
            if (is_string($params)) {
                $this->addListenerService($eventName, [$serviceId, $params], 0);
            } elseif (is_string($params[0])) {
                $this->addListenerService($eventName, [$serviceId, $params[0]], isset($params[1]) ? $params[1] : 0);
            } else {
                foreach ($params as $listener) {
                    $this->addListenerService(
                        $eventName,
                        [$serviceId, $listener[0]],
                        isset($listener[1]) ? $listener[1] : 0
                    );
                }
            }
        }
    }

    /**
     * Remove subscriber service.
     *
     * @param string $serviceId The service ID of the subscriber service
     * @param string $class     The service's class name
     */
    public function removeSubscriberService(string $serviceId, string $class)
    {
        $this->checkForInterface($class);

        foreach ($class::getSubscribedEvents() as $eventName => $params) {
            if (is_string($params)) {
                $this->removeListener($eventName, [$serviceId, $params]);
            } elseif (is_string($params[0])) {
                $this->removeListener($eventName, [$serviceId, $params[0]]);
            } else {
                foreach ($params as $listener) {
                    $this->removeListener($eventName, [$serviceId, $listener[0]]);
                }
            }
        }
    }

    /**
     * {@inheritdoc}.
     */
    public function dispatch($eventName, Event $event = null)
    {
        return $this->eventDispatcher->dispatch($eventName, $event);
    }

    /**
     * {@inheritdoc}.
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        return $this->eventDispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * {@inheritdoc}.
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        return $this->eventDispatcher->addSubscriber($subscriber);
    }

    /**
     * {@inheritdoc}.
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
        return $this->eventDispatcher->removeSubscriber($subscriber);
    }

    /**
     * {@inheritdoc}.
     */
    public function getListeners($eventName = null)
    {
        return $this->eventDispatcher->getListeners($eventName);
    }

    /**
     * {@inheritdoc}.
     */
    public function hasListeners($eventName = null)
    {
        return $this->eventDispatcher->hasListeners($eventName);
    }

    /**
     * {@inheritdoc}
     */
    public function getListenerPriority($eventName, $listener)
    {
        return $this->dispatcher->getListenerPriority($eventName, $listener);
    }

    /**
     * Checking if class has EventSubscriberInterface.
     *
     * @param string $class The service's class name (which must implement EventSubscriberInterface)
     *
     * @throws \InvalidArgumentException
     */
    protected function checkForInterface(string $class)
    {
        $rfc = new ReflectionClass($class);

        if (!$rfc->implementsInterface('Symfony\Component\EventDispatcher\EventSubscriberInterface')) {
            throw new InvalidArgumentException(
                sprintf('%s must implement Symfony\Component\EventDispatcher\EventSubscriberInterface', $class)
            );
        }
    }
}
