<?php
namespace Viserio\Mail\Transport;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Post\PostFile;
use Swift_Mime_Message;

class Mailgun extends AbstractTransport
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
        $this->url = $base . $this->domain . '/messages.mime';
    }

    /**
     * Send Email.
     *
     * @param \Swift_Mime_Message $message
     * @param string[]|null       $failedRecipients
     *
     * @return Log|null
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $options = ['auth' => ['api', $this->key]];

        $to = $this->getTo($message);
        $message->setBcc([]);

        if (version_compare(ClientInterface::VERSION, '6') === 1) {
            $options['multipart'] = [
                ['name' => 'to', 'contents' => $to],
                ['name' => 'message', 'contents' => $message->toString(), 'filename' => 'message.mime'],
            ];
        } else {
            $options['body'] = [
                'to' => $to,
                'message' => new PostFile('message', $message->toString()),
            ];
        }

        return $this->client->post($this->url, $options);
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
        $this->url = 'https://api.mailgun.net/v3/' . $domain . '/messages.mime';

        return $this->domain = $domain;
    }

    /**
     * Get the "to" payload field for the API request.
     *
     * @param \Swift_Mime_Message $message
     *
     * @return string
     */
    protected function getTo(Swift_Mime_Message $message)
    {
        $formatted = [];

        $contacts = array_merge(
            (array) $message->getTo(),
            (array) $message->getCc(),
            (array) $message->getBcc()
        );

        foreach ($contacts as $address => $display) {
            $formatted[] = $display ? $display . sprintf('<%s>', $address) : $address;
        }

        return implode(',', $formatted);
    }
}
