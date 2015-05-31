<?php

namespace Brainwave\Mail\Transport;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use GuzzleHttp\Client;

/**
 * Mandrill.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.1-dev
 */
class Mandrill implements \Swift_Transport
{
    /**
     * The Mandrill API key.
     *
     * @var string
     */
    protected $key;

    /**
     * Create a new Mandrill transport instance.
     *
     * @param string $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Is email sending started.
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * Start email sending.
     */
    public function start()
    {
        return true;
    }

    /**
     * Stop email sending.
     */
    public function stop()
    {
        return true;
    }

    /**
     * Send Email.
     *
     * @param \Swift_Mime_Message $message
     * @param string[]|null       $failedRecipients
     *
     * @return Log|null
     */
    public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $client = $this->getHttpClient();

        $client->post('https://mandrillapp.com/api/1.0/messages/send-raw.json', [
            'body' => [
                'key' => $this->key,
                'raw_message' => (string) $message,
                'async' => false,
                'to' => $this->getToAddresses($message),
            ],
        ]);
    }

    /**
     * Get all the addresses this email should be sent to,
     * including "to", "cc" and "bcc" addresses.
     *
     * @param \Swift_Mime_Message $message
     *
     * @return array
     */
    protected function getToAddresses(\Swift_Mime_Message $message)
    {
        $to = [];
        if ($message->getTo()) {
            $to = array_merge($to, array_keys($message->getTo()));
        }

        if ($message->getCc()) {
            $to = array_merge($to, array_keys($message->getCc()));
        }

        if ($message->getBcc()) {
            $to = array_merge($to, array_keys($message->getBcc()));
        }

        return $to;
    }

    /**
     * {@inheritdoc}
     */
    public function registerPlugin(\Swift_Events_EventListener $plugin)
    {
        //
    }

    /**
     * Get a new HTTP client instance.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        return new Client();
    }

    /**
     * Get the API key being used by the transport.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set the API key being used by the transport.
     *
     * @param string $key
     *
     * @return string
     */
    public function setKey($key)
    {
        return $this->key = $key;
    }
}
