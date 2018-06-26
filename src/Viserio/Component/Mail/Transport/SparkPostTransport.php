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
     * Spark api endpoint.
     *
     * @see https://developers.sparkpost.com/api/
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Create a new SparkPost transport instance.
     *
     * @param \GuzzleHttp\Client $client
     * @param string             $key
     * @param array              $options
     * @param null|string        $endpoint
     */
    public function __construct(Client $client, string $key, array $options = [], ?string $endpoint = null)
    {
        $this->key      = $key;
        $this->client   = $client;
        $this->options  = $options;
        $this->endpoint = $endpoint ?? 'https://api.sparkpost.com/api/v1/transmissions';
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

        if (\count($this->options) !== 0) {
            $options['json']['options'] = $this->options;
        }

        $response = $this->client->post($this->endpoint, $options);

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

        if ($message->getTo() !== null) {
            foreach ($message->getTo() as $email => $name) {
                $recipients[] = ['address' => \compact('name', 'email')];
            }
        }

        if ($message->getCc() !== null) {
            foreach ($message->getCc() as $email => $name) {
                $recipients[] = ['address' => \compact('name', 'email')];
            }
        }

        if ($message->getBcc() !== null) {
            foreach ($message->getBcc() as $email => $name) {
                $recipients[] = ['address' => \compact('name', 'email')];
            }
        }

        return $recipients;
    }
}
