<?php
namespace Viserio\Mail\Transport;

use GuzzleHttp\ClientInterface;
use Swift_Mime_Attachment;
use Swift_Mime_Headers_DateHeader;
use Swift_Mime_Headers_IdentificationHeader;
use Swift_Mime_Headers_OpenDKIMHeader;
use Swift_Mime_Headers_ParameterizedHeader;
use Swift_Mime_Headers_PathHeader;
use Swift_Mime_Headers_UnstructuredHeader;
use Swift_Mime_Message;

class Postmark extends Transport
{
    /**
     * Guzzle client instance.
     *
     * @var \GuzzleHttp\ClientInterface
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
     * @param \GuzzleHttp\ClientInterface $client
     * @param string                      $serverToken
     */
    public function __construct(ClientInterface $client, $serverToken)
    {
        $this->client      = $client;
        $this->serverToken = $serverToken;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        return $client->post('https://api.postmarkapp.com/email', [
            'headers' => [
                'X-Postmark-Server-Token' => $this->serverToken,
            ],
            'json' => $this->getMessagePayload($message),
        ]);

        return $this->client->post($this->url, $options);
    }

    /**
     * Convert email dictionary with emails and names
     * to array of emails with names.
     *
     * @param string[] $emails
     *
     * @return array
     */
    private function convertEmailsArray(array $emails)
    {
        $convertedEmails = [];
        foreach ($emails as $email => $name) {
            $convertedEmails[] = $name
            ? '"' . str_replace('"', '\\"', $name) . sprintf('\ <%s>', $email)
            : $email;
        }

        return $convertedEmails;
    }

    /**
     * Gets MIME parts that match the message type.
     * Excludes parts of type \Swift_Mime_Attachment as those
     * are handled later.
     *
     * @param \Swift_Mime_Message $message
     * @param string              $mimeType
     *
     * @return \Swift_Mime_MimeEntity|null
     */
    private function getMIMEPart(Swift_Mime_Message $message, $mimeType)
    {
        foreach ($message->getChildren() as $part) {
            if (strpos($part->getContentType(), $mimeType) === 0 &&  !($part instanceof Swift_Mime_Attachment)) {
                return $part;
            }
        }
    }

    /**
     * Convert a Swift Mime Message to a Postmark Payload.
     *
     * @param \Swift_Mime_Message $message
     *
     * @return array
     */
    private function getMessagePayload(Swift_Mime_Message $message)
    {
        $payload = [];
        $this->processRecipients($payload, $message);
        $this->processMessageParts($payload, $message);

        if ($message->getHeaders()) {
            $this->processHeaders($payload, $message);
        }

        return $payload;
    }

    /**
     * Applies the recipients of the message into the API Payload.
     *
     * @param array               $payload
     * @param \Swift_Mime_Message $message
     *
     * @return object|null
     */
    private function processRecipients(&$payload, $message)
    {
        $payload['From'] = implode(',', $this->convertEmailsArray($message->getFrom()));
        $payload['To'] = implode(',', $this->convertEmailsArray($message->getTo()));
        $payload['Subject'] = $message->getSubject();

        if ($cc = $message->getCc()) {
            $payload['Cc'] = implode(',', $this->convertEmailsArray($cc));
        }

        if ($reply_to = $message->getReplyTo()) {
            $payload['ReplyTo'] = implode(',', $this->convertEmailsArray($reply_to));
        }

        if ($bcc = $message->getBcc()) {
            $payload['Bcc'] = implode(',', $this->convertEmailsArray($bcc));
        }
    }

    /**
     * Applies the message parts and attachments
     * into the API Payload.
     *
     * @param array               $payload
     * @param \Swift_Mime_Message $message
     *
     * @return object|null
     */
    private function processMessageParts(&$payload, $message)
    {
        //Get the primary message.
        switch ($message->getContentType()) {
            case 'text/html':
            case 'multipart/alternative':
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
                    $payload['Attachments'][] = [
                        'Name' => $attachment->getFilename(),
                        'Content' => base64_encode($attachment->getBody()),
                        'ContentType' => $attachment->getContentType(),
                    ];
                }
            }
        }
    }

    /**
     * Applies the headers into the API Payload.
     *
     * @param array               $payload
     * @param \Swift_Mime_Message $message
     *
     * @return object|null
     */
    private function processHeaders(&$payload, $message)
    {
        $headers = [];

        foreach ($message->getHeaders()->getAll() as $key => $value) {
            $fieldName = $value->getFieldName();
            $excludedHeaders = ['Subject', 'Content-Type', 'MIME-Version', 'Date'];

            if (!in_array($fieldName, $excludedHeaders, true)) {
                if ($value instanceof Swift_Mime_Headers_UnstructuredHeader ||
                    $value instanceof Swift_Mime_Headers_OpenDKIMHeader) {
                    array_push($headers, [
                        'Name' => $fieldName,
                        'Value' => $value->getValue(),
                    ]);
                } elseif ($value instanceof Swift_Mime_Headers_DateHeader ||
                    $value instanceof Swift_Mime_Headers_IdentificationHeader ||
                    $value instanceof Swift_Mime_Headers_ParameterizedHeader ||
                    $value instanceof Swift_Mime_Headers_PathHeader) {
                    array_push($headers, [
                        'Name' => $fieldName,
                        'Value' => $value->getFieldBody(),
                    ]);

                    if ($value->getFieldName() === 'Message-ID') {
                        array_push($headers, [
                            'Name' => 'X-PM-KeepID',
                            'Value' => 'true',
                        ]);
                    }
                }
            }
        }

        $payload['Headers'] = $headers;
    }

    /**
     * Get the API key being used by the transport.
     *
     * @return string
     */
    public function getServerToken()
    {
        return $this->serverToken;
    }

    /**
     * Set the API Server Token being used by the transport.
     *
     * @param string $serverToken
     *
     * @return string
     */
    public function setServerToken($serverToken)
    {
        return $this->serverToken = $serverToken;
    }
}
