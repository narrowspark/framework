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

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Post\PostFile;

/**
 * Mailgun.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.1-dev
 */
class Mailgun implements \Swift_Transport
{
    /**
     * Guzzle client instance.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * The Mailgun API key.
     *
     * @var string
     */
    protected $key;

    /**
     * The Mailgun domain.
     *
     * @var string
     */
    protected $domain;

    /**
     * THe Mailgun API end-point.
     *
     * @var string
     */
    protected $url;

    /**
     * Create a new Mailgun transport instance.
     *
     * @param \GuzzleHttp\ClientInterface $client
     * @param string                      $key
     * @param string                      $base
     * @param string                      $domain
     */
    public function __construct(ClientInterface $client, $key, $base, $domain)
    {
        $this->client = $client;
        $this->key = $key;
        $this->domain = $domain;
        $this->url = $base.$this->domain.'/messages.mime';
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
        $options = ['auth' => ['api', $this->key]];

        if (version_compare(ClientInterface::VERSION, '6') === 1) {
            $options['multipart'] = [
                ['name' => 'to', 'contents' => $this->getTo($message)],
                ['name' => 'message', 'contents' => (string) $message, 'filename' => 'message.mime'],
            ];
        } else {
            $options['body'] = [
                'to' => $this->getTo($message),
                'message' => new PostFile('message', (string) $message),
            ];
        }

        return $this->client->post($this->url, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function registerPlugin(\Swift_Events_EventListener $plugin)
    {
        //
    }

    /**
     * Get the "to" payload field for the API request.
     *
     * @param \Swift_Mime_Message $message
     *
     * @return string
     */
    protected function getTo(\Swift_Mime_Message $message)
    {
        $formatted = [];

        $contacts = array_merge(
            (array) $message->getTo(),
            (array) $message->getCc(),
            (array) $message->getBcc()
        );

        foreach ($contacts as $address => $display) {
            $formatted[] = $display ? $display.sprintf('<%s>', $address) : $address;
        }

        return implode(',', $formatted);
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

    /**
     * Get the domain being used by the transport.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set the domain being used by the transport.
     *
     * @param string $domain
     *
     * @return string
     */
    public function setDomain($domain)
    {
        return $this->domain = $domain;
    }
}
