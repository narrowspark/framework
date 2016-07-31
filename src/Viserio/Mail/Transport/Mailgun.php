<?php
declare(strict_types=1);
namespace Viserio\Mail\Transport;

use GuzzleHttp\ClientInterface;
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
     * @param string                      $domain
     */
    public function __construct(ClientInterface $client, string $key, string $domain)
    {
        $this->client = $client;
        $this->key = $key;
        $this->setDomain($domain);
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $options = ['auth' => ['api', $this->key]];

        $to = $this->getTo($message);

        $message->setBcc([]);

        $options['multipart'] = [
            ['name' => 'to', 'contents' => $to],
            ['name' => 'message', 'contents' => $message->toString(), 'filename' => 'message.mime'],
        ];

        $this->client->post($this->url, $options);

        return $this->numberOfRecipients($message);
    }

    /**
     * Get the API key being used by the transport.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Set the API key being used by the transport.
     *
     * @param string $key
     *
     * @return $this
     */
    public function setKey(string $key): Mailgun
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get the domain being used by the transport.
     *
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Set the domain being used by the transport.
     *
     * @param string $domain
     *
     * @return $this
     */
    public function setDomain(string $domain): Mailgun
    {
        $this->url = 'https://api.mailgun.net/v3/' . $domain . '/messages.mime';

        $this->domain = $domain;

        return $this;
    }

    /**
     * Get the "to" payload field for the API request.
     *
     * @param \Swift_Mime_Message $message
     *
     * @return string
     */
    protected function getTo(Swift_Mime_Message $message): string
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
