<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Transport;

use GuzzleHttp\ClientInterface;
use Swift_Mime_Message;

class SparkPost extends AbstractTransport
{
    /**
     * Guzzle client instance.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * The SparkPost API key.
     *
     * @var string
     */
    protected $key;

    /**
     * Transmission options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Create a new SparkPost transport instance.
     *
     * @param \GuzzleHttp\ClientInterface $client
     * @param string                      $key
     * @param array                       $options
     */
    public function __construct(ClientInterface $client, string $key, array $options = [])
    {
        $this->key     = $key;
        $this->client  = $client;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $recipients = $this->getRecipients($message);

        $message->setBcc([]);

        $options = [
            'headers' => [
                'Authorization' => $this->key,
            ],
            'json' => [
                'recipients' => $recipients,
                'content'    => [
                    'email_rfc822' => $message->toString(),
                ],
            ],
        ];

        if (! empty($this->options)) {
            $options['json']['options'] = $this->options;
        }

        $this->client->post('https://api.sparkpost.com/api/v1/transmissions', $options);

        return $this->numberOfRecipients($message);
    }

    /**
     * Get the API key being used by the transport.
     *
     * @return string
     *
     * @codeCoverageIgnore
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
     *
     * @codeCoverageIgnore
     */
    public function setKey(string $key): SparkPost
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get the transmission options being used by the transport.
     *
     * @return array
     *
     * @codeCoverageIgnore
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set the transmission options being used by the transport.
     *
     * @param array $options
     *
     * @return $this
     *
     * @codeCoverageIgnore
     */
    public function setOptions(array $options): SparkPost
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get all the addresses this message should be sent to.
     *
     * Note that SparkPost still respects CC, BCC headers in raw message itself.
     *
     * @param \Swift_Mime_Message $message
     *
     * @return array
     */
    protected function getRecipients(Swift_Mime_Message $message): array
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

        $recipients = array_map(function ($address) {
            return compact('address');
        }, $to);

        return $recipients;
    }
}
