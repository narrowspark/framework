<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\DataCollector;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MessagesDataCollector extends AbstractDataCollector
{
    /**
     * Message name.
     *
     * @var string
     */
    protected $name;

    /**
     * Collection of all messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Create new messages collector.
     *
     * @param string $name
     */
    public function __construct(string $name = 'messages')
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
        $messages = $this->getMessages();

        $this->data = [
            'counted'  => \count($messages),
            'messages' => $messages,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'label' => 'Messages',
            'value' => $this->data['counted'],
        ];
    }

    /**
     * Adds a message.
     *
     * A message can be anything from an object to a string.
     *
     * @param mixed  $message
     * @param string $label
     *
     * @return void
     */
    public function addMessage($message, string $label = 'info'): void
    {
        if (! \is_string($message)) {
            $message = $this->cloneVar($message);
        }

        $this->messages[] = [
            'message' => \is_string($message) ? $message : $this->cloneVar($message),
            'label'   => $label,
            'time'    => \microtime(true),
        ];
    }

    /**
     * Returns collected messages.
     *
     * @return array
     */
    public function getMessages(): array
    {
        $messages = $this->messages;

        // sort messages by their timestamp
        \usort($messages, function ($a, $b) {
            if ($a['time'] === $b['time']) {
                return 0;
            }

            return $a['time'] < $b['time'] ? -1 : 1;
        });

        return $messages;
    }

    /**
     * Deletes all messages.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->messages = [];
    }
}
