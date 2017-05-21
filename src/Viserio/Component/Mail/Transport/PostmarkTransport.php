<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Transport;

use GuzzleHttp\Client;
use Swift_Mime_Attachment;
use Swift_Mime_Headers_DateHeader;
use Swift_Mime_Headers_IdentificationHeader;
use Swift_Mime_Headers_OpenDKIMHeader;
use Swift_Mime_Headers_ParameterizedHeader;
use Swift_Mime_Headers_PathHeader;
use Swift_Mime_Headers_UnstructuredHeader;
use Swift_Mime_SimpleMessage;
use Swift_MimePart;

class PostmarkTransport extends AbstractTransport
{
    /**
     * Guzzle client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * The Postmark Server Token key.
     *
     * @var string
     */
    protected $serverToken;

    /**
     * Create a new Postmark transport instance.
     *
     * @param \GuzzleHttp\Client $client
     * @param string             $serverToken
     */
    public function __construct(Client $client, $serverToken)
    {
        $this->client      = $client;
        $this->serverToken = $serverToken;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null): int
    {
        $this->beforeSendPerformed($message);

        $version = phpversion() ?? 'Unknown PHP version';
        $os      = PHP_OS ?? 'Unknown OS';

        $this->client->post('https://api.postmarkapp.com/email', [
            'headers' => [
                'X-Postmark-Server-Token' => $this->serverToken,
                'Content-Type'            => 'application/json',
                'User-Agent'              => "postmark (PHP Version: $version, OS: $os)",
            ],
            'json' => $this->getMessagePayload($message),
        ]);

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }

    /**
     * {@inheritdoc}
     */
    public function ping(): bool
    {
        return true;
    }

    /**
     * Get the API key being used by the transport.
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getServerToken(): string
    {
        return $this->serverToken;
    }

    /**
     * Set the API Server Token being used by the transport.
     *
     * @param string $serverToken
     *
     * @return \Viserio\Component\Mail\Transport\Postmark
     *
     * @codeCoverageIgnore
     */
    public function setServerToken(string $serverToken): self
    {
        $this->serverToken = $serverToken;

        return $this;
    }

    /**
     * Convert email dictionary with emails and names
     * to array of emails with names.
     *
     * @param string[] $emails
     *
     * @return array
     */
    protected function convertEmailsArray(array $emails): array
    {
        $convertedEmails = [];

        foreach ($emails as $email => $name) {
            $convertedEmails[] = $name ?
            '"' . str_replace('"', '\\"', $name) . "\" <{$email}>" :
            $email;
        }

        return $convertedEmails;
    }

    /**
     * Gets MIME parts that match the message type.
     * Excludes parts of type \Swift_Mime_Attachment as those
     * are handled later.
     *
     * @param \Swift_Mime_SimpleMessage $message
     * @param string                    $mimeType
     *
     * @return \Swift_MimePart|null
     */
    protected function getMIMEPart(Swift_Mime_SimpleMessage $message, $mimeType): ?Swift_MimePart
    {
        foreach ($message->getChildren() as $part) {
            if (mb_strpos($part->getContentType(), $mimeType) === 0 &&
                ! ($part instanceof Swift_Mime_Attachment)
            ) {
                return $part;
            }
        }

        return null;
    }

    /**
     * Convert a Swift Mime Message to a Postmark Payload.
     *
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return array
     */
    protected function getMessagePayload(Swift_Mime_SimpleMessage $message): array
    {
        $payload = [];

        $payload = $this->processRecipients($payload, $message);
        $payload = $this->processMessageParts($payload, $message);

        if ($message->getHeaders()) {
            $payload = $this->processHeaders($payload, $message);
        }

        return $payload;
    }

    /**
     * Applies the recipients of the message into the API Payload.
     *
     * @param array                     $payload
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return array
     */
    protected function processRecipients(array $payload, Swift_Mime_SimpleMessage $message): array
    {
        $payload['From']    = implode(',', $this->convertEmailsArray($message->getFrom()));
        $payload['To']      = implode(',', $this->convertEmailsArray($message->getTo()));
        $payload['Subject'] = $message->getSubject();

        if ($cc = $message->getCc()) {
            $payload['Cc'] = implode(',', $this->convertEmailsArray($cc));
        }

        if ($replyTo = $message->getReplyTo()) {
            $payload['ReplyTo'] = implode(',', $this->convertEmailsArray($replyTo));
        }

        if ($bcc = $message->getBcc()) {
            $payload['Bcc'] = implode(',', $this->convertEmailsArray($bcc));
        }

        return $payload;
    }

    /**
     * Applies the message parts and attachments
     * into the API Payload.
     *
     * @param array                     $payload
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return array
     */
    protected function processMessageParts(array $payload, Swift_Mime_SimpleMessage $message): array
    {
        //Get the primary message.
        switch ($message->getContentType()) {
            case 'text/html':
            case 'multipart/alternative':
            case 'multipart/mixed':
                $payload['HtmlBody'] = $message->getBody();
                break;
            default:
                $payload['TextBody'] = $message->getBody();
                break;
        }

        // Provide an alternate view from the secondary parts.
        if ($plain = $this->getMIMEPart($message, 'text/plain')) {
            $payload['TextBody'] = $plain->getBody();
        }

        if ($html = $this->getMIMEPart($message, 'text/html')) {
            $payload['HtmlBody'] = $html->getBody();
        }

        if ($message->getChildren()) {
            $payload['Attachments'] = [];

            foreach ($message->getChildren() as $attachment) {
                if (is_object($attachment) && $attachment instanceof Swift_Mime_Attachment) {
                    $attachments = [
                        'Name'        => $attachment->getFilename(),
                        'Content'     => base64_encode($attachment->getBody()),
                        'ContentType' => $attachment->getContentType(),
                    ];

                    if ($attachment->getDisposition() !== 'attachment' &&
                        $attachment->getId() !== null
                    ) {
                        $attachments['ContentID'] = 'cid:' . $attachment->getId();
                    }

                    $payload['Attachments'][] = $attachments;
                }
            }
        }

        return $payload;
    }

    /**
     * Applies the headers into the API Payload.
     *
     * @param array                     $payload
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return array
     */
    protected function processHeaders(array $payload, Swift_Mime_SimpleMessage $message): array
    {
        $headers = [];

        foreach ($message->getHeaders()->getAll() as $key => $value) {
            $fieldName       = $value->getFieldName();
            $excludedHeaders = ['Subject', 'Content-Type', 'MIME-Version', 'Date'];

            if (! in_array($fieldName, $excludedHeaders)) {
                if ($value instanceof Swift_Mime_Headers_UnstructuredHeader ||
                    $value instanceof Swift_Mime_Headers_OpenDKIMHeader
                ) {
                    array_push($headers, [
                        'Name'  => $fieldName,
                        'Value' => $value->getValue(),
                    ]);
                } elseif ($value instanceof Swift_Mime_Headers_DateHeader ||
                    $value instanceof Swift_Mime_Headers_IdentificationHeader ||
                    $value instanceof Swift_Mime_Headers_ParameterizedHeader ||
                    $value instanceof Swift_Mime_Headers_PathHeader
                ) {
                    array_push($headers, [
                        'Name'  => $fieldName,
                        'Value' => $value->getFieldBody(),
                    ]);

                    if ($value->getFieldName() === 'Message-ID') {
                        array_push($headers, [
                            'Name'  => 'X-PM-KeepID',
                            'Value' => 'true',
                        ]);
                    }
                }
            }
        }

        $payload['Headers'] = $headers;

        return $payload;
    }
}
