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

use Closure;

interface Mailer
{
    /**
     * Set the global from address and name.
     */
    public function alwaysFrom(string $address, ?string $name = null): void;

    /**
     * Set the global to address and name.
     */
    public function alwaysTo(string $address, ?string $name = null): void;

    /**
     * Set the global reply-to address and name.
     *
     * @param null|string $name
     */
    public function alwaysReplyTo(string $address, $name = null): void;

    /**
     * Send a new message when only a raw text part.
     */
    public function raw(string $text, $callback): int;

    /**
     * Send a new message when only a plain part.
     */
    public function plain(string $view, array $data, $callback): int;

    /**
     * Send a new message using a view.
     *
     * @param array|string        $view
     * @param null|Closure|string $callback
     */
    public function send($view, array $data = [], $callback = null): int;

    /**
     * Get the array of failed recipients.
     */
    public function failures(): array;
}
