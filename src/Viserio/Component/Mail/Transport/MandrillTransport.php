<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Mail\Transport;

use GuzzleHttp\Client;
use Swift_Mime_SimpleMessage;

class MandrillTransport extends AbstractTransport
{
    /**
     * Guzzle client instance.
     *
     * @var \GuzzleHttp\Client
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
     */
    public function __construct(Client $client, string $key)
    {
        $this->client = $client;
        $this->key = $key;
    }

    /**
     * Get the API key being used by the transport.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Set the API key being used by the transport.
     *
     * @return $this
     */
    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null): int
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
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function ping(): bool
    {
        return true;
    }

    /**
     * Get all the addresses this email should be sent to,
     * including "to", "cc" and "bcc" addresses.
     */
    protected function getToAddresses(Swift_Mime_SimpleMessage $message): array
    {
        $to = [];

        $getTo = $message->getTo();

        if (\is_array($getTo)) {
            $to = \array_merge($to, \array_keys($getTo));
        }

        $cc = $message->getCc();

        if (\is_array($cc)) {
            $to = \array_merge($to, \array_keys($cc));
        }

        $bcc = $message->getBcc();

        if (\is_array($bcc)) {
            $to = \array_merge($to, \array_keys($bcc));
        }

        return $to;
    }
}
