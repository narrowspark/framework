<?php
declare(strict_types=1);
namespace Viserio\Component\Mail;

use Swift_Attachment;
use Swift_Image;
use Swift_Mime_Attachment;
use Swift_Mime_SimpleMessage;
use Viserio\Component\Contract\Mail\Message as MessageContract;

/**
 * @mixin \Swift_Mime_SimpleMessage
 */
class Message implements MessageContract
{
    /**
     * The Swift Message instance.
     *
     * @var \Swift_Mime_SimpleMessage
     */
    protected $swift;

    /**
     * Create a new message instance.
     *
     * @param \Swift_Mime_SimpleMessage $swift
     */
    public function __construct(Swift_Mime_SimpleMessage $swift)
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
        return \call_user_func_array([$this->swift, $method], $parameters);
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

        $this->addAddresses($address, $name, 'To');

        return $this;
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

        $this->addAddresses($address, $name, 'Cc');

        return $this;
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

        $this->addAddresses($address, $name, 'Bcc');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function replyTo(string $address, string $name = null): MessageContract
    {
        $this->addAddresses($address, $name, 'ReplyTo');

        return $this;
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

        $this->prepAttachment($attachment, $options);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function attachData(string $data, string $name, array $options = []): MessageContract
    {
        $attachment = $this->createAttachmentFromData($data, $name);

        $this->prepAttachment($attachment, $options);

        return $this;
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
        $image = new Swift_Image($data, $name, $contentType);

        return $this->swift->embed($image);
    }

    /**
     * Get the underlying Swift Message instance.
     *
     * @return \Swift_Mime_SimpleMessage
     */
    public function getSwiftMessage(): Swift_Mime_SimpleMessage
    {
        return $this->swift;
    }

    /**
     * Add a recipient to the message.
     *
     * @param array|string $address
     * @param string       $name
     * @param string       $type
     *
     * @return void
     */
    protected function addAddresses($address, string $name, string $type): void
    {
        if (\is_array($address)) {
            $set = \sprintf('set%s', $type);
            $this->swift->$set($address, $name);

            return;
        }

        $add = \sprintf('add%s', $type);
        $this->swift->$add($address, $name);
    }

    /**
     * Create a Swift Attachment instance.
     *
     * @param string $file
     *
     * @return \Swift_Mime_Attachment
     */
    protected function createAttachmentFromPath(string $file): Swift_Mime_Attachment
    {
        return Swift_Attachment::fromPath($file);
    }

    /**
     * Create a Swift Attachment instance from data.
     *
     * @param string $data
     * @param string $name
     *
     * @return \Swift_Mime_Attachment
     */
    protected function createAttachmentFromData(string $data, string $name): Swift_Mime_Attachment
    {
        return new Swift_Attachment($data, $name);
    }

    /**
     * Prepare and attach the given attachment.
     *
     * @param \Swift_Mime_Attachment $attachment
     * @param array                  $options
     *
     * @return void
     */
    protected function prepAttachment(Swift_Mime_Attachment $attachment, array $options = []): void
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
    }
}
