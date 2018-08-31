<?php
declare(strict_types=1);
namespace Viserio\Component\Mail;

use Closure;
use InvalidArgumentException;
use Spatie\Macroable\Macroable;
use Swift_Mailer;
use Swift_Mime_SimpleMessage;
use Viserio\Component\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contract\Events\Traits\EventManagerAwareTrait;
use Viserio\Component\Contract\Mail\Exception\UnexpectedValueException;
use Viserio\Component\Contract\Mail\Mailer as MailerContract;
use Viserio\Component\Contract\Mail\Message as MessageContract;
use Viserio\Component\Contract\View\Traits\ViewAwareTrait;
use Viserio\Component\Mail\Event\MessageSendingEvent;
use Viserio\Component\Mail\Event\MessageSentEvent;
use Viserio\Component\Support\Traits\InvokerAwareTrait;

class Mailer implements MailerContract
{
    use ContainerAwareTrait;
    use EventManagerAwareTrait;
    use InvokerAwareTrait;
    use Macroable;
    use ViewAwareTrait;

    /**
     * The Swift Mailer instance.
     *
     * @var \Swift_Mailer
     */
    protected $swift;

    /**
     * The global from address and name.
     *
     * @var array
     */
    protected $from = [];

    /**
     * Set the global to address and name.
     *
     * @var array
     */
    protected $to = [];

    /**
     * The global reply-to address and name.
     *
     * @var array
     */
    protected $replyTo;

    /**
     * Array of failed recipients.
     *
     * @var array
     */
    protected $failedRecipients = [];

    /**
     * Resolved options.
     *
     * @var array
     */
    protected $options;

    /**
     * Create a new Mailer instance.
     *
     * @param \Swift_Mailer $swiftMailer
     * @param array         $data
     */
    public function __construct(Swift_Mailer $swiftMailer, array $data)
    {
        $this->options = $data;

        // If a "from" address is set, we will set it on the mailer so that all mail
        // messages sent by the applications will utilize the same "from" address
        // on each one, which makes the developer's life a lot more convenient.
        $from = $this->options['from'] ?? null;

        if (\is_array($from) && isset($from['address'], $from['name'])) {
            $this->alwaysFrom($from['address'], $from['name']);
        }

        $to = $this->options['to'] ?? null;

        if (\is_array($to) && isset($to['address'], $to['name'])) {
            $this->alwaysTo($to['address'], $to['name']);
        }

        $this->swift = $swiftMailer;
    }

    /**
     * {@inheritdoc}
     */
    public function alwaysFrom(string $address, string $name = null): void
    {
        $this->from = \compact('address', 'name');
    }

    /**
     * {@inheritdoc}
     */
    public function alwaysTo(string $address, string $name = null): void
    {
        $this->to = \compact('address', 'name');
    }

    /**
     * Set the global reply-to address and name.
     *
     * @param string      $address
     * @param null|string $name
     *
     * @return void
     */
    public function alwaysReplyTo(string $address, $name = null): void
    {
        $this->replyTo = \compact('address', 'name');
    }

    /**
     * {@inheritdoc}
     */
    public function raw(string $text, $callback): int
    {
        return $this->send(['raw' => $text], [], $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function plain(string $view, array $data, $callback): int
    {
        return $this->send(['text' => $view], $data, $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function send($view, array $data = [], $callback = null): int
    {
        // First we need to parse the view, which could either be a string or an array
        // containing both an HTML and plain text versions of the view which should
        // be used when sending an e-mail. We will extract both of them out here.
        [$view, $plain, $raw] = $this->parseView($view);

        $data['message'] = $message = $this->createMessage();

        // Once we have retrieved the view content for the e-mail we will set the body
        // of this message using the HTML type, which will provide a simple wrapper
        // to creating view based emails that are able to receive arrays of data.
        $this->addContent($message, $view, $plain, $raw, $data);

        if ($callback !== null) {
            $this->callMessageBuilder($callback, $message);
        }

        // If a global to address has been specified we will override
        // any recipient addresses previously set and use this one instead.
        if (isset($this->to['address'])) {
            $message->to($this->to['address'], $this->to['name'], true);
            $message->cc(null, null, true);
            $message->bcc(null, null, true);
        }

        $recipients = $this->sendSwiftMessage($message->getSwiftMessage());

        if ($this->eventManager !== null) {
            $this->eventManager->trigger(new MessageSentEvent($this, $message->getSwiftMessage(), $recipients));
        }

        return $recipients;
    }

    /**
     * {@inheritdoc}
     */
    public function failures(): array
    {
        return $this->failedRecipients;
    }

    /**
     * Get the Swift Mailer instance.
     *
     * @return \Swift_Mailer
     */
    public function getSwiftMailer(): Swift_Mailer
    {
        return $this->swift;
    }

    /**
     * Parse the given view name or array.
     *
     * @param array|string $view
     *
     * @throws \Viserio\Component\Contract\Mail\Exception\UnexpectedValueException
     *
     * @return array
     */
    protected function parseView($view): array
    {
        if (\is_string($view)) {
            return [$view, null, null];
        }

        // If the given view is an array with numeric keys, we will just assume that
        // both a "pretty" and "plain" view were provided, so we will return this
        // array as is, since must should contain both views with numeric keys.
        if (\is_array($view) && isset($view[0])) {
            return [$view[0], $view[1], null];
        }

        // If the view is an array, but doesn't contain numeric keys, we will assume
        // the the views are being explicitly specified and will extract them via
        // named keys instead, allowing the developers to use one or the other.
        if (\is_array($view)) {
            return [
                $view['html'] ?? null,
                $view['text'] ?? null,
                $view['raw'] ?? null,
            ];
        }

        throw new UnexpectedValueException('Invalid view.');
    }

    /**
     * Add the content to a given message.
     *
     * @param \Viserio\Component\Contract\Mail\Message $message
     * @param null|string                              $view
     * @param null|string                              $plain
     * @param null|string                              $raw
     * @param array                                    $data
     */
    protected function addContent(
        MessageContract $message,
        ?string $view,
        ?string $plain,
        ?string $raw,
        array $data
    ): void {
        if ($view !== null) {
            $message->setBody($this->createView($view, $data), 'text/html');
        }

        if ($plain !== null) {
            $method = $view !== null ? 'addPart' : 'setBody';

            $message->{$method}($this->createView($plain, $data), 'text/plain');
        }

        if ($raw !== null) {
            $method = ($view !== null || $plain !== null) ? 'addPart' : 'setBody';

            $message->{$method}($raw, 'text/plain');
        }
    }

    /**
     * Send a Swift Message instance.
     *
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return int
     */
    protected function sendSwiftMessage(Swift_Mime_SimpleMessage $message): int
    {
        if (! $this->shouldSendMessage($message)) {
            return 0;
        }

        try {
            return $this->swift->send($message, $this->failedRecipients);
        } finally {
            $this->forceReconnecting();
        }
    }

    /**
     * Determines if the message can be sent.
     *
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return bool
     */
    protected function shouldSendMessage(Swift_Mime_SimpleMessage $message): bool
    {
        if (! $this->eventManager) {
            return true;
        }

        return $this->eventManager->trigger(new MessageSendingEvent($this, $message)) !== false;
    }

    /**
     * Force the transport to re-connect.
     *
     * This will prevent errors in daemon queue situations.
     *
     * @return void
     */
    protected function forceReconnecting(): void
    {
        $this->swift->getTransport()->stop();
    }

    /**
     * Create a new message instance.
     *
     * @return \Viserio\Component\Contract\Mail\Message
     */
    protected function createMessage(): MessageContract
    {
        $message = new Message($this->swift->createMessage());

        // If a global from address has been specified we will set it on every message
        // instances so the developer does not have to repeat themselves every time
        // they create a new message. We will just go ahead and push the address.
        if (isset($this->from['address'])) {
            $message->from($this->from['address'], $this->from['name']);
        }

        // When a global reply address was specified we will set this on every message
        // instance so the developer does not have to repeat themselves every time
        // they create a new message. We will just go ahead and push this address.
        if (! empty($this->replyTo['address'])) {
            $message->replyTo($this->replyTo['address'], $this->replyTo['name']);
        }

        return $message;
    }

    /**
     * Call the provided message builder.
     *
     * @param null|\Closure|string                     $callback
     * @param \Viserio\Component\Contract\Mail\Message $message
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    protected function callMessageBuilder($callback, MessageContract $message)
    {
        if ($callback instanceof Closure) {
            return $callback($message);
        }

        if ($this->container !== null) {
            return $this->getInvoker()->call($callback)->mail($message);
        }

        throw new InvalidArgumentException('Callback is not valid.');
    }

    /**
     * Creates a view string for the email body.
     *
     * @param string $view
     * @param array  $data
     *
     * @return string
     */
    protected function createView(string $view, array $data): string
    {
        if ($this->viewFactory !== null) {
            return $this->viewFactory->create($view, $data)->render();
        }

        return \vsprintf($view, $data);
    }
}
