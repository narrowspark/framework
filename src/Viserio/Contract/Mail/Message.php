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

namespace Viserio\Contract\Mail;

interface Message
{
    /**
     * Add a "from" address to the message.
     */
    public function from(string $address, ?string $name = null): self;

    /**
     * Set the "sender" of the message.
     */
    public function sender(string $address, ?string $name = null): self;

    /**
     * Set the "return path" of the message.
     */
    public function returnPath(string $address): self;

    /**
     * Add a recipient to the message.
     *
     * @param array|string $address
     * @param bool         $override Will force ignoring the previous recipients
     */
    public function to($address, ?string $name = null, bool $override = false): self;

    /**
     * Add a Chronos copy to the message.
     *
     * @param array|string $address
     * @param bool         $override Will force ignoring the previous recipients
     */
    public function cc($address, ?string $name = null, bool $override = false): self;

    /**
     * Add a blind Chronos copy to the message.
     *
     * @param array|string $address
     * @param bool         $override Will force ignoring the previous recipients
     */
    public function bcc($address, ?string $name = null, bool $override = false): self;

    /**
     * Add a reply to address to the message.
     */
    public function replyTo(string $address, ?string $name = null): self;

    /**
     * Set the subject of the message.
     */
    public function subject(string $subject): self;

    /**
     * Set the message priority level.
     */
    public function priority(int $level): self;

    /**
     * Attach a file to the message.
     */
    public function attach(string $file, array $options = []): self;

    /**
     * Attach in-memory data as an attachment.
     */
    public function attachData(string $data, string $name, array $options = []): self;

    /**
     * Embed a file in the message and get the CID.
     */
    public function embed(string $file): string;

    /**
     * Embed in-memory data in the message and get the CID.
     */
    public function embedData(string $data, string $name, ?string $contentType = null): string;
}
