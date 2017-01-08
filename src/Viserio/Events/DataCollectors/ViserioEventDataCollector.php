<?php
declare(strict_types=1);
namespace Viserio\Events\DataCollectors;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;
use Viserio\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\WebProfiler\PanelAware as PanelAwareContract;
use Viserio\WebProfiler\DataCollectors\TimeDataCollector;

class ViserioEventDataCollector extends TimeDataCollector implements PanelAwareContract
{
    use EventsAwareTrait;

    /**
     * Create new events collector instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     * @param \Viserio\Contracts\Events\EventManager   $events
     */
    public function __construct(ServerRequestInterface $serverRequest, EventManagerContract $events)
    {
        parent::__construct($serverRequest);

        $this->events = $events;
    }

    /**
     * {@inheritdoc}
     */
    public function getMenuPosition(): string
    {
        return 'left';
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
        parent::collect($serverRequest, $response);

        $this->events->attach('#', [$this, 'onWildcardEvent']);

        $this->data['events'] = count($this->data['measures']);
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon'  => file_get_contents(__DIR__ . '/Resources/icons/ic_filter_list_white_24px.svg'),
            'label' => 'Events',
            'value' => $this->data['events'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        $html = '';

        return $html;
    }

    /**
     * [onWildcardEvent description].
     *
     * @return void
     * @param  mixed $event
     */
    public function onWildcardEvent($event)
    {
        $name = $event->getName();
        $time = microtime(true);

        // Get the arguments passed to the event
        $params = []; //$this->prepareParams(func_get_args());

        // Find all listeners for the current event
        foreach ($this->events->getListeners($name) as $i => $listener) {
            // Check if it's an object + method name
            if (is_array($listener) && count($listener) > 1 && is_object($listener[0])) {
                list($class, $method) = $listener;
                error_log(var_dump($listener));
                // Skip this class itself
                if ($class instanceof static) {
                    continue;
                }

                // Format the listener to readable format
                $listener = get_class($class) . '@' . $method;
            } elseif ($listener instanceof Closure) {
                $reflector = new ReflectionFunction($listener);

                // Skip our own listeners
                if ($reflector->getNamespaceName() == 'Viserio\Events\DataCollectors') {
                    continue;
                }

                // Format the closure to a readable format
                $filename = ltrim(str_replace(base_path(), '', $reflector->getFileName()), '/');
                $listener = $reflector->getName() . ' (' . $filename . ':' . $reflector->getStartLine() . '-' . $reflector->getEndLine() . ')';
            }

            $params['listeners.' . $i] = $listener;
        }

        $this->addMeasure($name, $time, $time, $params);
    }
}
