<?php
declare(strict_types=1);
namespace Viserio\Mail;

use Swift_Attachment;
use Swift_Image;
use Swift_Message;

class Message
{
    /**
     * The Swift Message instance.
     *
     * @var \Swift_Message
     */
    protected $swift;

    /**
     * Create a new message instance.
     *
     * @param \Swift_Message $swift
     */
    public function __construct(Swift_Message $swift)
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
        $callable = [$this->swift, $method];

        return call_user_func_array($callable, $parameters);
    }

    /**
     * Add a "from" address to the message.
     *
     * @param string      $address
     * @param string|null $name
     *
     * @return \Viserio\Mail\Message
     */
    public function from($address, $name = null)
    {
        $this->swift->setFrom($address, $name);

        return $this;
    }

    /**
     * Set the "sender" of the message.
     *
     * @param string      $address
     * @param string|null $name
     *
     * @return \Viserio\Mail\Message
     */
    public function sender($address, $name = null)
    {
        $this->swift->setSender($address, $name);

        return $this;
    }

    /**
     * Set the "return path" of the message.
     *
     * @param string $address
     *
     * @return \Viserio\Mail\Message
     */
    public function returnPath($address)
    {
        $this->swift->setReturnPath($address);

        return $this;
    }

    /**
     * Add a recipient to the message.
     *
     * @param string|array $address
     * @param string|null  $name
     * @param bool         $override Will force ignoring the previous recipients
     *
     * @return \Viserio\Mail\Message
     */
    public function to($address, $name = null, $override = false)
    {
        if ($override) {
            return $this->swift->setTo($address, $name);
        }

        return $this->addAddresses($address, $name, 'To');
    }

    /**
     * Add a carbon copy to the message.
     *
     * @param string      $address
     * @param string|null $name
     *
     * @return \Viserio\Mail\Message
     */
    public function cc($address, $name = null)
    {
        return $this->addAddresses($address, $name, 'Cc');
    }

    /**
     * Add a blind carbon copy to the message.
     *
     * @param string      $address
     * @param string|null $name
     *
     * @return \Viserio\Mail\Message
     */
    public function bcc($address, $name = null)
    {
        return $this->addAddresses($address, $name, 'Bcc');
    }

    /**
     * Add a reply to address to the message.
     *
     * @param string      $address
     * @param string|null $name
     *
     * @return \Viserio\Mail\Message
     */
    public function replyTo($address, $name = null)
    {
        return $this->addAddresses($address, $name, 'ReplyTo');
    }

    /**
     * Set the subject of the message.
     *
     * @param string $subject
     *
     * @return \Viserio\Mail\Message
     */
    public function subject($subject)
    {
        $this->swift->setSubject($subject);

        return $this;
    }

    /**
     * Set the message priority level.
     *
     * @param int $level
     *
     * @return \Viserio\Mail\Message
     */
    public function priority($level)
    {
        $this->swift->setPriority($level);

        return $this;
    }

    /**
     * Attach a file to the message.
     *
     * @param string $file
     * @param array  $options
     *
     * @return \Viserio\Mail\Message
     */
    public function attach($file, array $options = [])
    {
        $attachment = $this->createAttachmentFromPath($file);

        return $this->prepAttachment($attachment, $options);
    }

    /**
     * Attach in-memory data as an attachment.
     *
     * @param string $data
     * @param string $name
     * @param array  $options
     *
     * @return \Viserio\Mail\Message
     */
    public function attachData($data, $name, array $options = [])
    {
        $attachment = $this->createAttachmentFromData($data, $name);

        return $this->prepAttachment($attachment, $options);
    }

    /**
     * Embed a file in the message and get the CID.
     *
     * @param string $file
     *
     * @return string
     */
    public function embed($file)
    {
        return $this->swift->embed(Swift_Image::fromPath($file));
    }

    /**
     * Embed in-memory data in the message and get the CID.
     *
     * @param string      $data
     * @param string      $name
     * @param string|null $contentType
     *
     * @return string
     */
    public function embedData($data, $name, $contentType = null)
    {
        $image = Swift_Image::newInstance($data, $name, $contentType);

        return $this->swift->embed($image);
    }

    /**
     * Get the underlying Swift Message instance.
     *
     * @return \Swift_Message
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
     * @return \Viserio\Mail\Message
     */
    protected function addAddresses($address, $name, $type)
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
    protected function createAttachmentFromPath($file)
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
    protected function createAttachmentFromData($data, $name)
    {
        return Swift_Attachment::newInstance($data, $name);
    }

    /**
     * Prepare and attach the given attachment.
     *
     * @param \Swift_Attachment $attachment
     * @param array             $options
     *
     * @return \Viserio\Mail\Message
     */
    protected function prepAttachment(Swift_Attachment $attachment, $options = [])
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
