<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\Mail;

interface Message
{
    /**
     * Add a "from" address to the message.
     *
     * @param string      $address
     * @param null|string $name
     *
     * @return self
     */
    public function from(string $address, ?string $name = null): self;

    /**
     * Set the "sender" of the message.
     *
     * @param string      $address
     * @param null|string $name
     *
     * @return self
     */
    public function sender(string $address, ?string $name = null): self;

    /**
     * Set the "return path" of the message.
     *
     * @param string $address
     *
     * @return self
     */
    public function returnPath(string $address): self;

    /**
     * Add a recipient to the message.
     *
     * @param array|string $address
     * @param null|string  $name
     * @param bool         $override Will force ignoring the previous recipients
     *
     * @return self
     */
    public function to($address, ?string $name = null, bool $override = false): self;

    /**
     * Add a Chronos copy to the message.
     *
     * @param array|string $address
     * @param null|string  $name
     * @param bool         $override Will force ignoring the previous recipients
     *
     * @return self
     */
    public function cc($address, ?string $name = null, bool $override = false): self;

    /**
     * Add a blind Chronos copy to the message.
     *
     * @param array|string $address
     * @param null|string  $name
     * @param bool         $override Will force ignoring the previous recipients
     *
     * @return self
     */
    public function bcc($address, ?string $name = null, bool $override = false): self;

    /**
     * Add a reply to address to the message.
     *
     * @param string      $address
     * @param null|string $name
     *
     * @return self
     */
    public function replyTo(string $address, ?string $name = null): self;

    /**
     * Set the subject of the message.
     *
     * @param string $subject
     *
     * @return self
     */
    public function subject(string $subject): self;

    /**
     * Set the message priority level.
     *
     * @param int $level
     *
     * @return self
     */
    public function priority(int $level): self;

    /**
     * Attach a file to the message.
     *
     * @param string $file
     * @param array  $options
     *
     * @return self
     */
    public function attach(string $file, array $options = []): self;

    /**
     * Attach in-memory data as an attachment.
     *
     * @param string $data
     * @param string $name
     * @param array  $options
     *
     * @return self
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
    public function embedData(string $data, string $name, ?string $contentType = null): string;
}
