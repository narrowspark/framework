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
     * @return $this
     */
    public function from(string $address, string $name = null): Message;

    /**
     * Set the "sender" of the message.
     *
     * @param string      $address
     * @param null|string $name
     *
     * @return $this
     */
    public function sender(string $address, string $name = null): Message;

    /**
     * Set the "return path" of the message.
     *
     * @param string $address
     *
     * @return $this
     */
    public function returnPath(string $address): Message;

    /**
     * Add a recipient to the message.
     *
     * @param array|string $address
     * @param null|string  $name
     * @param bool         $override Will force ignoring the previous recipients
     *
     * @return $this
     */
    public function to($address, string $name = null, bool $override = false): Message;

    /**
     * Add a Chronos copy to the message.
     *
     * @param array|string $address
     * @param null|string  $name
     * @param bool         $override Will force ignoring the previous recipients
     *
     * @return $this
     */
    public function cc($address, string $name = null, bool $override = false): Message;

    /**
     * Add a blind Chronos copy to the message.
     *
     * @param array|string $address
     * @param null|string  $name
     * @param bool         $override Will force ignoring the previous recipients
     *
     * @return $this
     */
    public function bcc($address, string $name = null, bool $override = false): Message;

    /**
     * Add a reply to address to the message.
     *
     * @param string      $address
     * @param null|string $name
     *
     * @return $this
     */
    public function replyTo(string $address, string $name = null): Message;

    /**
     * Set the subject of the message.
     *
     * @param string $subject
     *
     * @return $this
     */
    public function subject(string $subject): Message;

    /**
     * Set the message priority level.
     *
     * @param int $level
     *
     * @return $this
     */
    public function priority(int $level): Message;

    /**
     * Attach a file to the message.
     *
     * @param string $file
     * @param array  $options
     *
     * @return $this
     */
    public function attach(string $file, array $options = []): Message;

    /**
     * Attach in-memory data as an attachment.
     *
     * @param string $data
     * @param string $name
     * @param array  $options
     *
     * @return $this
     */
    public function attachData(string $data, string $name, array $options = []): Message;

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
