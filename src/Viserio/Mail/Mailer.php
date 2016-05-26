<?php
namespace Viserio\Mail;

use Closure;
use Exception;
use InvalidArgumentException;
use Narrowspark\Arr\StaticArr as Arr;
use Psr\Log\LoggerInterface;
use Swift_Mailer;
use Swift_Message;
use Swift_Transport_AbstractSmtpTransport;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Viserio\Contracts\Mail\Mailer as MailerContract;
use Viserio\Contracts\View\Factory;

class Mailer implements MailerContract
{
    /**
     * The view factory instance.
     *
     * @var \Viserio\Contracts\View\Factory
     */
    protected $views;

    /**
     * The event dispatcher instance.
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $events;

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
    protected $from;

    /**
     * The log writer instance.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Indicates if the actual sending is disabled.
     *
     * @var bool
     */
    protected $pretending = false;

    /**
     * Array of failed recipients.
     *
     * @var array
     */
    protected $failedRecipients = [];

    /**
     * Try to reset swift in case of failure.
     *
     * @var bool
     */
    protected $resetSwift = false;

    /**
     * Set the global to address and name.
     *
     * @var array
     */
    protected $to;

    /**
     * Create a new Mailer instance.
     *
     * @param \Swift_Mailer                                               $swift
     * @param \Viserio\Contracts\View\Factory                             $view
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $events
     */
    public function __construct(
        Swift_Mailer $swift,
        Factory $view,
        EventDispatcherInterface $events
    ) {
        $this->swift = $swift;
        $this->views = $view;
        $this->events = $events;
    }

    /**
     * Enable to reset swift mailer on failure.
     *
     * @var void
     */
    public function resetSwift($reset = false)
    {
        $this->resetSwift = $reset;
    }

    /**
     * Set the global from address and name.
     *
     * @param string      $address
     * @param string|null $name
     */
    public function alwaysFrom($address, $name = null)
    {
        $this->from = compact($address, $name);
    }

    /**
     * Set the global to address and name.
     *
     * @param string      $address
     * @param string|null $name
     */
    public function alwaysTo($address, $name = null)
    {
        $this->to = compact('address', 'name');
    }

    /**
     * Send a new message when only a raw text part.
     *
     * @param string $text
     * @param mixed  $callback
     *
     * @return int
     */
    public function raw($text, $callback)
    {
        return $this->send(['raw' => $text], [], $callback);
    }

    /**
     * Send a new message when only a plain part.
     *
     * @param string $view
     * @param array  $data
     * @param mixed  $callback
     *
     * @return int
     */
    public function plain($view, array $data, $callback)
    {
        return $this->send(['text' => $view], $data, $callback);
    }

    /**
     * Send a new message using a view.
     *
     * @param string|array $view
     * @param array        $data
     * @param \Closure     $callback
     *
     * @return int
     */
    public function send($view, array $data, Closure $callback)
    {
        $this->forceReconnection();

        // First we need to parse the view, which could either be a string or an array
        // containing both an HTML and plain text versions of the view which should
        // be used when sending an e-mail. We will extract both of them out here.
        list($view, $plain, $raw) = $this->parseView($view);

        $data['message'] = $message = $this->createMessage();

        $this->callMessageBuilder($callback, $message);

        // Once we have retrieved the view content for the e-mail we will set the body
        // of this message using the HTML type, which will provide a simple wrapper
        // to creating view based emails that are able to receive arrays of data.
        $this->addContent($message, $view, $plain, $raw, $data);

        // If a global to address has been specified we will override
        // any recipient addresses previously set and use this one instead.
        if (isset($this->to['address'])) {
            $message->to($this->to['address'], $this->to['name'], true);
        }

        $message = $message->getSwiftMessage();

        return $this->sendSwiftMessage($message);
    }

    /**
     * Get the array of failed recipients.
     *
     * @return array
     */
    public function failures()
    {
        return $this->failedRecipients;
    }

    /**
     * Get the view factory instance.
     *
     * @return \Viserio\Contracts\View\Factory
     */
    public function getViewFactory()
    {
        return $this->views;
    }

    /**
     * Tell the mailer to not really send messages.
     *
     * @param bool $value
     */
    public function pretend($value = true)
    {
        $this->pretending = $value;
    }

    /**
     * Check if the mailer is pretending to send messages.
     *
     * @return bool
     */
    public function isPretending()
    {
        return $this->pretending;
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
     * Set the log writer instance.
     *
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return \Viserio\Mail\Mailer
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Force the transport to re-connect.
     *
     * This will prevent errors in daemon queue situations.
     */
    protected function forceReconnection()
    {
        $this->getSwiftMailer()->getTransport()->stop();
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
     * Get the Swift Mailer Transport instance.
     *
     * @return \Swift_Transport|null
     */
    protected function getSwiftMailerTransport()
    {
        if ($this->swift instanceof Swift_Mailer) {
            return $this->swift->getTransport();
        }
    }

    /**
     * Add the content to a given message.
     *
     * @param \Viserio\Mail\Message $message
     * @param string                $view
     * @param string                $plain
     * @param string                $raw
     * @param array                 $data
     *
     * @method setBody()
     * @method addPart()
     */
    protected function addContent($message, $view, $plain, $raw, $data)
    {
        if (isset($view)) {
            $message->setBody($this->getView($view, $data), 'text/html');
        }

        if (isset($plain)) {
            $message->addPart($this->getView($plain, $data), 'text/plain');
        }

        if (isset($raw)) {
            $message->addPart($raw, 'text/plain');
        }
    }

    /**
     * Send a Swift Message instance.
     *
     * @param \Swift_Message $message
     *
     * @return int
     */
    protected function sendSwiftMessage($message)
    {
        if ($this->events) {
            $this->events->addListener('mailer.sending', [$message]);
        }

        if (! $this->pretending) {
            if ($this->resetSwift) {
                // Fail-safe restart before email TXN
                // Required for queued mail sending using daemon
                $this->resetSwiftTransport();
            }

            return $this->swift->send($message, $this->failedRecipients);
        } elseif (isset($this->logger)) {
            $this->logMessage($message);

            return 1;
        }

        return 0;
    }

    /**
     * Log that a message was sent.
     *
     * @param \Swift_Message $message
     */
    protected function logMessage($message)
    {
        $emails = implode(', ', array_keys((array) $message->getTo()));

        $this->logger->info(sprintf('Pretending to mail message to: %s', $emails));
    }

    /**
     * Create a new message instance.
     *
     * @return \Viserio\Mail\Message
     */
    protected function createMessage()
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
     * @param \Closure              $callback
     * @param \Viserio\Mail\Message $message
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    protected function callMessageBuilder(Closure $callback, $message)
    {
        if ($callback instanceof Closure) {
            return call_user_func($callback, $message);
        }

        throw new InvalidArgumentException('Callback is not valid.');
    }

    /**
     * Render the given view.
     *
     * @param string $view
     * @param array  $data
     *
     * @return \Viserio\Contracts\View\View
     */
    protected function getView($view, array $data)
    {
        return $this->views->make($view, $data);
    }

    /**
     * Reset Swift Mailer SMTP transport adapter.
     */
    protected function resetSwiftTransport()
    {
        if (! $transport = $this->getSwiftMailerTransport()) {
            return;
        }

        try {
            // Send RESET to restart the SMTP status and check if it's ready for running
            if ($transport instanceof Swift_Transport_AbstractSmtpTransport) {
                $transport->reset();
            }
        } catch (Exception $e) {
            $this->tryResetSwiftTransport($transport);
        }
    }

    /**
     * In case of failure - let's try to restart it.
     *
     * @param \Swift_Transport_AbstractSmtpTransport $transport
     */
    protected function tryResetSwiftTransport($transport)
    {
        try {
            $transport->stop();
        } catch (Exception $e) {
            // Just start it then...
        }

        $transport->start();
    }
}
