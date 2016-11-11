<?php
declare(strict_types=1);
namespace Viserio\Mail\Transport;

use GuzzleHttp\ClientInterface;
use Swift_Mime_Message;

class Mandrill extends AbstractTransport
{
    /**
     * Guzzle client instance.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * The Mandrill API key.
     *
     * @var string
     */
    protected $key;

    /**
     * Create a new Mandrill transport instance.
     *
     * @param \GuzzleHttp\ClientInterface $client
     * @param string                      $key
     */
    public function __construct(ClientInterface $client, string $key)
    {
        $this->client = $client;
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $data = [
            'key' => $this->key,
            'to' => $this->getToAddresses($message),
            'raw_message' => $message->toString(),
            'async' => false,
        ];

        $options = ['form_params' => $data];

        $this->client->post('https://mandrillapp.com/api/1.0/messages/send-raw.json', $options);

        $this->sendPerformed($message);

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
     * @return \Viserio\Mail\Transport\Mandrill
     *
     * @codeCoverageIgnore
     */
    public function setKey(string $key): Mandrill
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get all the addresses this email should be sent to,
     * including "to", "cc" and "bcc" addresses.
     *
     * @param \Swift_Mime_Message $message
     *
     * @return array
     */
    protected function getToAddresses(Swift_Mime_Message $message): array
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
}
