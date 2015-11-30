<?php
namespace Viserio\Mail\Transport;

use Aws\Ses\SesClient;
use Swift_Mime_Message;

class Ses extends Transport
{
    /**
     * The Amazon SES instance.
     *
     * @var \Aws\Ses\SesClient
     */
    protected $ses;

    /**
     * Create a new SES transport instance.
     *
     * @param \Aws\Ses\SesClient $ses
     */
    public function __construct(SesClient $ses)
    {
        $this->ses = $ses;
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
        return $this->ses->sendRawEmail([
            'Source' => key($message->getSender()),
            'Destinations' => $this->getTo($message),
            'RawMessage' => [
                'Data' => $message->toString(),
            ],
        ]);
    }

    /**
     * Get the "to" payload field for the API request.
     *
     * @param \Swift_Mime_Message $message
     *
     * @return array
     */
    protected function getTo(Swift_Mime_Message $message)
    {
        $destinations = [];
        $contacts = array_merge(
            (array) $message->getTo(),
            (array) $message->getCc(),
            (array) $message->getBcc()
        );

        foreach ($contacts as $address => $display) {
            $destinations[] = $address;
        }

        return $destinations;
    }
}
