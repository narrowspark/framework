<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Transport;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Swift_Mime_SimpleMessage;

class SparkPostTransport extends AbstractTransport
{
    /**
     * Guzzle client instance.
     *
     * @var \GuzzleHttp\Client
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
     * @param \GuzzleHttp\Client $client
     * @param string             $key
     * @param array              $options
     */
    public function __construct(Client $client, string $key, array $options = [])
    {
        $this->key     = $key;
        $this->client  = $client;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null): int
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

        $response = $this->client->post('https://api.sparkpost.com/api/v1/transmissions', $options);

        $message->getHeaders()->addTextHeader('X-SparkPost-Transmission-ID', $this->getTransmissionId($response));

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
     * Get the transmission options being used by the transport.
     *
     * @return array
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
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get the transmission ID from the response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return null|string
     */
    protected function getTransmissionId(ResponseInterface $response): ?string
    {
        $object = \json_decode($response->getBody()->getContents());

        foreach (['results', 'id'] as $segment) {
            if (! \is_object($object) || ! isset($object->{$segment})) {
                return null;
            }

            $object = $object->{$segment};
        }

        return $object;
    }

    /**
     * Get all the addresses this message should be sent to.
     *
     * Note that SparkPost still respects CC, BCC headers in raw message itself.
     *
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return array
     */
    protected function getRecipients(Swift_Mime_SimpleMessage $message): array
    {
        $recipients = [];

        foreach ((array) $message->getTo() as $email => $name) {
            $recipients[] = ['address' => \compact('name', 'email')];
        }

        foreach ((array) $message->getCc() as $email => $name) {
            $recipients[] = ['address' => \compact('name', 'email')];
        }

        foreach ((array) $message->getBcc() as $email => $name) {
            $recipients[] = ['address' => \compact('name', 'email')];
        }

        return $recipients;
    }
}
