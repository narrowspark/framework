<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Transport;

use GuzzleHttp\Client;
use Swift_Mime_SimpleMessage;

class MailgunTransport extends AbstractTransport
{
    /**
     * Guzzle client instance.
     *
     * @var \GuzzleHttp\Client
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
     * Mailgun base url.
     *
     * @see https://documentation.mailgun.com/en/latest/api-intro.html#mailgun-regions
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Create a new Mailgun transport instance.
     *
     * @param \GuzzleHttp\Client $client
     * @param string             $key
     * @param string             $domain
     * @param null|string        $baseUrl
     */
    public function __construct(Client $client, string $key, string $domain, ?string $baseUrl = null)
    {
        $this->client  = $client;
        $this->key     = $key;
        $this->baseUrl = $baseUrl ?? 'https://api.mailgun.net';

        $this->setDomain($domain);
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
    public function setKey(string $key): self
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
    public function setDomain(string $domain): self
    {
        $this->url = $this->baseUrl . '/v3/' . $domain . '/messages.mime';

        $this->domain = $domain;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null): int
    {
        $this->beforeSendPerformed($message);

        $options = ['auth' => ['api', $this->key]];

        $options['multipart'] = [
            ['name' => 'to', 'contents' => $this->getTo($message)],
            ['name' => 'cc', 'contents' => $this->getCc($message)],
            ['name' => 'bcc', 'contents' => $this->getBcc($message)],
            ['name' => 'message', 'contents' => $message->toString(), 'filename' => 'message.mime'],
        ];

        $this->client->post($this->url, $options);

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function ping(): bool
    {
        return true;
    }

    /**
     * Get the "to" payload field for the API request.
     *
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return string
     */
    protected function getTo(Swift_Mime_SimpleMessage $message): string
    {
        return $this->formatAddress($message->getTo() ?? []);
    }

    /**
     * Get the "cc" payload field for the API request.
     *
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return string
     */
    protected function getCc(Swift_Mime_SimpleMessage $message): string
    {
        return $this->formatAddress($message->getCc() ?? []);
    }

    /**
     * Get the "bcc" payload field for the API request.
     *
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return string
     */
    protected function getBcc(Swift_Mime_SimpleMessage $message): string
    {
        return $this->formatAddress($message->getBcc() ?? []);
    }

    /**
     * Get Comma-Separated Address (with name, if available) for the API request.
     *
     * @param array $contacts
     *
     * @return string
     */
    protected function formatAddress(array $contacts): string
    {
        $formatted = [];

        foreach ($contacts as $address => $display) {
            $formatted[] = $display ? $display . \sprintf('<%s>', $address) : $address;
        }

        return \implode(',', $formatted);
    }
}
