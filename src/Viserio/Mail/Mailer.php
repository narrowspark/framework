<?php
declare(strict_types=1);
namespace Viserio\Mail;

use Closure;
use InvalidArgumentException;
use Narrowspark\Arr\StaticArr as Arr;
use Swift_Mailer;
use Swift_Message;
use Swift_Mime_Message;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\Mail\Mailer as MailerContract;
use Viserio\Contracts\Mail\Message as MessageContract;
use Viserio\Contracts\View\Factory as ViewFactoryContract;
use Viserio\Contracts\View\Traits\ViewAwareTrait;

class Mailer implements MailerContract
{
    use EventsAwareTrait;
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
     * @param \Swift_Mailer                   $swift
     * @param \Viserio\Contracts\View\Factory $views
     */
    public function __construct(Swift_Mailer $swift, ViewFactoryContract $views)
    {
        $this->swift = $swift;
        $this->views = $views;
    }

    /**
     * {@inheritdoc}
     */
    public function alwaysFrom(string $address, string $name = null)
    {
        $this->from = compact($address, $name);
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

        $this->callMessageBuilder($callback, $message);

        // If a global to address has been specified we will override
        // any recipient addresses previously set and use this one instead.
        if (isset($this->to['address'])) {
            $message->to($this->to['address'], $this->to['name'], true);
        }

        return $this->sendSwiftMessage($message->getSwiftMessage());
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
    protected function parseView($view)
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
                Arr::get($view, 'html'),
                Arr::get($view, 'text'),
                Arr::get($view, 'raw'),
            ];
        }

        throw new InvalidArgumentException('Invalid view.');
    }

    /**
     * Add the content to a given message.
     *
     * @param \Viserio\Mail\Message $message
     * @param string|null           $view
     * @param string|null           $plain
     * @param string|null           $raw
     * @param array                 $data
     */
    protected function addContent(MessageContract $message, $view, $plain, $raw, array $data)
    {
        if ($view !== null) {
            $message->setBody($this->views->create($view, $data)->render(), 'text/html');
        }

        if ($plain !== null) {
            $method = $view !== null ? 'addPart' : 'setBody';

            $message->$method($this->views->create($plain, $data)->render(), 'text/plain');
        }

        if ($raw !== null) {
            $method = ($view !== null || $plain !== null) ? 'addPart' : 'setBody';

            $message->$method($raw, 'text/plain');
        }
    }

    /**
     * Send a Swift Message instance.
     *
     * @param \Swift_Mime_Message $message
     *
     * @return int
     */
    protected function sendSwiftMessage(Swift_Mime_Message $message): int
    {
        if ($this->events) {
            $this->events->emit('events.message.sending', $message);
        }

        try {
            return $this->swift->send($message, $this->failedRecipients);
        } finally {
            $this->swift->getTransport()->stop();
        }
    }

    /**
     * Create a new message instance.
     *
     * @return \Viserio\Mail\Message
     */
    protected function createMessage(): MessageContract
    {
        $message = new Message(new Swift_Message());

        // If a global from address has been specified we will set it on every message
        // instances so the developer does not have to repeat themselves every time
        // they create a new message. We will just go ahead and push the address.
        if (! empty($this->from['address'])) {
            $message->from($this->from['address'], $this->from['name']);
        }

        return $message;
    }

    /**
     * Call the provided message builder.
     *
     * @param \Closure|string       $callback
     * @param \Viserio\Mail\Message $message
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    protected function callMessageBuilder($callback, $message)
    {
        if ($callback instanceof Closure) {
            return call_user_func($callback, $message);
        }

        throw new InvalidArgumentException('Callback is not valid.');
    }
}
