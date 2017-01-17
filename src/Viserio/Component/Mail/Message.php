<?php
declare(strict_types=1);
namespace Viserio\Component\Mail;

use Swift_Attachment;
use Swift_Image;
use Swift_Mime_Message;
use Viserio\Component\Contracts\Mail\Message as MessageContract;

class Message implements MessageContract
{
    /**
     * The Swift Message instance.
     *
     * @var \Swift_Mime_Message
     */
    protected $swift;

    /**
     * Create a new message instance.
     *
     * @param \Swift_Mime_Message $swift
     */
    public function __construct(Swift_Mime_Message $swift)
    {
        $this->swift = $swift;
    }

    /**
     * Dynamically pass missing methods to the Swift instance.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->swift, $method], $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function from(string $address, string $name = null): MessageContract
    {
        $this->swift->setFrom($address, $name);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function sender(string $address, string $name = null): MessageContract
    {
        $this->swift->setSender($address, $name);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function returnPath(string $address): MessageContract
    {
        $this->swift->setReturnPath($address);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function to($address, string $name = null, bool $override = false): MessageContract
    {
        if ($override) {
            $this->swift->setTo($address, $name);

            return $this;
        }

        return $this->addAddresses($address, $name, 'To');
    }

    /**
     * {@inheritdoc}
     */
    public function cc($address, string $name = null, bool $override = false): MessageContract
    {
        if ($override) {
            $this->swift->setCc($address, $name);

            return $this;
        }

        return $this->addAddresses($address, $name, 'Cc');
    }

    /**
     * {@inheritdoc}
     */
    public function bcc($address, string $name = null, bool $override = false): MessageContract
    {
        if ($override) {
            $this->swift->setBcc($address, $name);

            return $this;
        }

        return $this->addAddresses($address, $name, 'Bcc');
    }

    /**
     * {@inheritdoc}
     */
    public function replyTo(string $address, string $name = null): MessageContract
    {
        return $this->addAddresses($address, $name, 'ReplyTo');
    }

    /**
     * {@inheritdoc}
     */
    public function subject(string $subject): MessageContract
    {
        $this->swift->setSubject($subject);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function priority(int $level): MessageContract
    {
        $this->swift->setPriority($level);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function attach(string $file, array $options = []): MessageContract
    {
        $attachment = $this->createAttachmentFromPath($file);

        return $this->prepAttachment($attachment, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function attachData(string $data, string $name, array $options = []): MessageContract
    {
        $attachment = $this->createAttachmentFromData($data, $name);

        return $this->prepAttachment($attachment, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function embed(string $file): string
    {
        return $this->swift->embed(Swift_Image::fromPath($file));
    }

    /**
     * {@inheritdoc}
     */
    public function embedData(string $data, string $name, string $contentType = null): string
    {
        $image = Swift_Image::newInstance($data, $name, $contentType);

        return $this->swift->embed($image);
    }

    /**
     * {@inheritdoc}
     */
    public function getSwiftMessage()
    {
        return $this->swift;
    }

    /**
     * Add a recipient to the message.
     *
     * @param string|array $address
     * @param string       $name
     * @param string       $type
     *
     * @return $this
     */
    protected function addAddresses($address, string $name, string $type): MessageContract
    {
        if (is_array($address)) {
            $set = sprintf('set%s', $type);
            $this->swift->$set($address, $name);
        } else {
            $add = sprintf('add%s', $type);
            $this->swift->$add($address, $name);
        }

        return $this;
    }

    /**
     * Create a Swift Attachment instance.
     *
     * @param string $file
     *
     * @return \Swift_Attachment
     */
    protected function createAttachmentFromPath(string $file): Swift_Attachment
    {
        return Swift_Attachment::fromPath($file);
    }

    /**
     * Create a Swift Attachment instance from data.
     *
     * @param string $data
     * @param string $name
     *
     * @return \Swift_Attachment
     */
    protected function createAttachmentFromData(string $data, string $name): Swift_Attachment
    {
        return Swift_Attachment::newInstance($data, $name);
    }

    /**
     * Prepare and attach the given attachment.
     *
     * @param \Swift_Attachment $attachment
     * @param array             $options
     *
     * @return $this
     */
    protected function prepAttachment(Swift_Attachment $attachment, array $options = []): MessageContract
    {
        // First we will check for a MIME type on the message, which instructs the
        // mail client on what type of attachment the file is so that it may be
        // downloaded correctly by the user. The MIME option is not required.
        if (isset($options['mime'])) {
            $attachment->setContentType($options['mime']);
        }

        // If an alternative name was given as an option, we will set that on this
        // attachment so that it will be downloaded with the desired names from
        // the developer, otherwise the default file names will get assigned.
        if (isset($options['as'])) {
            $attachment->setFilename($options['as']);
        }

        $this->swift->attach($attachment);

        return $this;
    }
}
