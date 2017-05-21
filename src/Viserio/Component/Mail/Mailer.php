<?php
declare(strict_types=1);
namespace Viserio\Component\Mail;

use Closure;
use InvalidArgumentException;
use Swift_Mailer;
use Swift_Mime_SimpleMessage;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Component\Contracts\Mail\Mailer as MailerContract;
use Viserio\Component\Contracts\Mail\Message as MessageContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\View\Traits\ViewAwareTrait;
use Viserio\Component\Mail\Events\MessageSendingEvent;
use Viserio\Component\Mail\Events\MessageSentEvent;
use Viserio\Component\OptionsResolver\Traits\ConfigurationTrait;
use Viserio\Component\Support\Traits\InvokerAwareTrait;
use Viserio\Component\Support\Traits\MacroableTrait;

class Mailer implements MailerContract, RequiresComponentConfigContract
{
    use ContainerAwareTrait;
    use ConfigurationTrait;
    use EventsAwareTrait;
    use InvokerAwareTrait;
    use MacroableTrait;
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
     * Array of failed recipients.
     *
     * @var array
     */
    protected $failedRecipients = [];

    /**
     * Create a new Mailer instance.
     *
     * @param \Swift_Mailer                                  $swiftMailer
     * @param \Interop\Container\ContainerInterface|iterable $data
     */
    public function __construct(Swift_Mailer $swiftMailer, $data)
    {
        $this->configureOptions($data);

        // If a "from" address is set, we will set it on the mailer so that all mail
        // messages sent by the applications will utilize the same "from" address
        // on each one, which makes the developer's life a lot more convenient.
        $from = $this->options['from'] ?? null;

        if (is_array($from) && isset($from['address'], $from['name'])) {
            $this->alwaysFrom($from['address'], $from['name']);
        }

        $to = $this->options['to'] ?? null;

        if (is_array($to) && isset($to['address'], $to['name'])) {
            $this->alwaysTo($to['address'], $to['name']);
        }

        $this->swift = $swiftMailer;
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'mail'];
    }

    /**
     * {@inheritdoc}
     */
    public function alwaysFrom(string $address, string $name = null)
    {
        $this->from = compact('address', 'name');
    }

    /**
     * {@inheritdoc}
     */
    public function alwaysTo(string $address, string $name = null)
    {
        $this->to = compact('address', 'name');
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
        list($view, $plain, $raw) = $this->parseView($view);

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
            $message->cc($this->to['address'], $this->to['name'], true);
            $message->bcc($this->to['address'], $this->to['name'], true);
        }

        $recipients = $this->sendSwiftMessage($message->getSwiftMessage());

        if ($this->events !== null) {
            $this->events->trigger(new MessageSentEvent($this, $message->getSwiftMessage(), $recipients));
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
     * Set the Swift Mailer instance.
     *
     * @param \Swift_Mailer $swift
     */
    public function setSwiftMailer(Swift_Mailer $swift)
    {
        $this->swift = $swift;
    }

    /**
     * Get the Swift Mailer instance.
     *
     * @return \Swift_Mailer
     */
    public function getSwiftMailer()
    {
        return $this->swift;
    }

    /**
     * Parse the given view name or array.
     *
     * @param string|array $view
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function parseView($view): array
    {
        if (is_string($view)) {
            return [$view, null, null];
        }

        // If the given view is an array with numeric keys, we will just assume that
        // both a "pretty" and "plain" view were provided, so we will return this
        // array as is, since must should contain both views with numeric keys.
        if (is_array($view) && isset($view[0])) {
            return [$view[0], $view[1], null];
            // If the view is an array, but doesn't contain numeric keys, we will assume
            // the the views are being explicitly specified and will extract them via
            // named keys instead, allowing the developers to use one or the other.
        } elseif (is_array($view)) {
            return [
                $view['html'] ?? null,
                $view['text'] ?? null,
                $view['raw'] ?? null,
            ];
        }

        throw new InvalidArgumentException('Invalid view.');
    }

    /**
     * Add the content to a given message.
     *
     * @param \Viserio\Component\Mail\Message $message
     * @param string|null                     $view
     * @param string|null                     $plain
     * @param string|null                     $raw
     * @param array                           $data
     */
    protected function addContent(MessageContract $message, ?string $view, ?string $plain, ?string $raw, array $data)
    {
        if ($view !== null) {
            $message->setBody($this->createView($view, $data), 'text/html');
        }

        if ($plain !== null) {
            $method = $view !== null ? 'addPart' : 'setBody';

            $message->$method($this->createView($plain, $data), 'text/plain');
        }

        if ($raw !== null) {
            $method = ($view !== null || $plain !== null) ? 'addPart' : 'setBody';

            $message->$method($raw, 'text/plain');
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
        if (! $this->events) {
            return true;
        }

        return $this->events->trigger(new MessageSendingEvent($this, $message)) !== false;
    }

    /**
     * Force the transport to re-connect.
     *
     * This will prevent errors in daemon queue situations.
     *
     * @return void
     */
    protected function forceReconnecting()
    {
        $this->swift->getTransport()->stop();
    }

    /**
     * Create a new message instance.
     *
     * @return \Viserio\Component\Mail\Message
     */
    protected function createMessage(): MessageContract
    {
        $message = new Message($this->swift->createMessage('message'));

        // If a global from address has been specified we will set it on every message
        // instances so the developer does not have to repeat themselves every time
        // they create a new message. We will just go ahead and push the address.
        if (isset($this->from['address'])) {
            $message->from($this->from['address'], $this->from['name']);
        }

        return $message;
    }

    /**
     * Call the provided message builder.
     *
     * @param \Closure|string|null            $callback
     * @param \Viserio\Component\Mail\Message $message
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    protected function callMessageBuilder($callback, $message)
    {
        if ($callback instanceof Closure) {
            return $callback($message);
        } elseif ($this->container !== null) {
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
        if ($this->views !== null) {
            return $this->getViewFactory()->create($view, $data)->render();
        }

        return vsprintf($view, $data);
    }
}
