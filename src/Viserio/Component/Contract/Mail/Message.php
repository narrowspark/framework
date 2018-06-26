<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Mail;

interface Message
{
    /**
     * Add a "from" address to the message.
     *
     * @param string      $address
     * @param null|string $name
     *
     * @return \Viserio\Component\Contract\Mail\Message
     */
    public function from(string $address, string $name = null): self;

    /**
     * Set the "sender" of the message.
     *
     * @param string      $address
     * @param null|string $name
     *
     * @return \Viserio\Component\Contract\Mail\Message
     */
    public function sender(string $address, string $name = null): self;

    /**
     * Set the "return path" of the message.
     *
     * @param string $address
     *
     * @return \Viserio\Component\Contract\Mail\Message
     */
    public function returnPath(string $address): self;

    /**
     * Add a recipient to the message.
     *
     * @param array|string $address
     * @param null|string  $name
     * @param bool         $override Will force ignoring the previous recipients
     *
     * @return \Viserio\Component\Contract\Mail\Message
     */
    public function to($address, string $name = null, bool $override = false): self;

    /**
     * Add a Chronos copy to the message.
     *
     * @param array|string $address
     * @param null|string  $name
     * @param bool         $override Will force ignoring the previous recipients
     *
     * @return \Viserio\Component\Contract\Mail\Message
     */
    public function cc($address, string $name = null, bool $override = false): self;

    /**
     * Add a blind Chronos copy to the message.
     *
     * @param array|string $address
     * @param null|string  $name
     * @param bool         $override Will force ignoring the previous recipients
     *
     * @return \Viserio\Component\Contract\Mail\Message
     */
    public function bcc($address, string $name = null, bool $override = false): self;

    /**
     * Add a reply to address to the message.
     *
     * @param string      $address
     * @param null|string $name
     *
     * @return \Viserio\Component\Contract\Mail\Message
     */
    public function replyTo(string $address, string $name = null): self;

    /**
     * Set the subject of the message.
     *
     * @param string $subject
     *
     * @return \Viserio\Component\Contract\Mail\Message
     */
    public function subject(string $subject): self;

    /**
     * Set the message priority level.
     *
     * @param int $level
     *
     * @return \Viserio\Component\Contract\Mail\Message
     */
    public function priority(int $level): self;

    /**
     * Attach a file to the message.
     *
     * @param string $file
     * @param array  $options
     *
     * @return \Viserio\Component\Contract\Mail\Message
     */
    public function attach(string $file, array $options = []): self;

    /**
     * Attach in-memory data as an attachment.
     *
     * @param string $data
     * @param string $name
     * @param array  $options
     *
     * @return \Viserio\Component\Contract\Mail\Message
     */
    public function attachData(string $data, string $name, array $options = []): self;

    /**
     * Embed a file in the message and get the CID.
     *
     * @param string $file
     *
     * @return string
     */
    public function embed(string $file): string;

    /**
     * Embed in-memory data in the message and get the CID.
     *
     * @param string      $data
     * @param string      $name
     * @param null|string $contentType
     *
     * @return string
     */
    public function embedData(string $data, string $name, string $contentType = null): string;
}
