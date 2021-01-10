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

namespace Viserio\Contract\Session;

use JsonSerializable;
use Psr\Http\Message\ServerRequestInterface;
use SessionHandlerInterface;

interface Store extends JsonSerializable
{
    public const METADATA_NAMESPACE = '__metadata__';

    /**
     * Starts the session storage.
     * It should be called only once at the beginning. If called for existing
     * session it overwrites it (clears all values etc).
     *
     * @return bool true if session started
     */
    public function start(): bool;

    /**
     * Opens the session (for a given request).
     *
     * If called earlier, then second (and next ones) call does nothing.
     *
     * @throws \Viserio\Contract\Session\Exception\SuspiciousOperationException if fingerprints dont match
     *
     * @return bool true if session started
     */
    public function open(): bool;

    /**
     * Sets the session ID.
     */
    public function setId(string $id): void;

    /**
     * Returns the session ID.
     *
     * @return null|string the session ID
     */
    public function getId(): ?string;

    /**
     * Sets the session name.
     *
     * @throws \Viserio\Contract\Session\Exception\InvalidArgumentException
     */
    public function setName(string $name): void;

    /**
     * Returns the session name.
     *
     * @return string the session name
     */
    public function getName(): string;

    /**
     * Time after session is regenerated (in seconds).
     */
    public function getTtl(): int;

    /**
     * Is session expired?
     */
    public function isExpired(): bool;

    /**
     * Invalidates the current session.
     *
     * Clears all session attributes and flashes and regenerates the
     * session and deletes the old session from persistence.
     *
     * @return bool true if session invalidated, false if error
     */
    public function invalidate(): bool;

    /**
     * Migrates the current session to a new session id while maintaining all
     * session attributes.
     *
     * @param bool $destroy whether to delete the old session or leave it to garbage collection
     *
     * @return bool true if session migrated, false if error
     */
    public function migrate(bool $destroy = false): bool;

    /**
     * Force the session to be saved and closed.
     *
     * This method is generally not required for real sessions as
     * the session will be automatically saved at the end of
     * code execution.
     */
    public function save(): void;

    /**
     * Checks if an attribute is defined.
     *
     * @param string $name The attribute name
     *
     * @return bool true if the attribute is defined, false otherwise
     */
    public function has(string $name): bool;

    /**
     * Returns an attribute.
     *
     * @param string $name    The attribute name
     * @param mixed  $default the default value if not found
     */
    public function get(string $name, $default = null);

    /**
     * Sets an attribute.
     */
    public function set(string $name, $value): void;

    /**
     * Push a value onto a session array.
     */
    public function push(string $key, $value): void;

    /**
     * Removes an attribute.
     *
     * @return mixed The removed value or null when it does not exist
     */
    public function remove(string $name);

    /**
     * Returns attributes.
     *
     * @return array Attributes
     */
    public function getAll(): array;

    /**
     * Clears all attributes.
     */
    public function clear(): void;

    /**
     * Checks if the session was started.
     */
    public function isStarted(): bool;

    /**
     * Set the request limit for a session.
     */
    public function setIdRequestsLimit(int $limit): void;

    /**
     * Shows the counted request for session.
     */
    public function getRequestsCount(): int;

    /**
     * Specifies the number of seconds after which session
     * will be automatically expired.
     */
    public function setIdLiveTime(int $ttl): void;

    /**
     * Gets last trace timestamp.
     */
    public function getLastTrace(): ?int;

    /**
     * Gets first trace timestamp.
     */
    public function getFirstTrace(): ?int;

    /**
     * Gets last (id) regeneration timestamp.
     */
    public function getRegenerationTrace(): ?int;

    /**
     * Age the flash data for the session.
     */
    public function ageFlashData(): void;

    /**
     * Flash a key / value pair to the session.
     */
    public function flash(string $key, $value): void;

    /**
     * Flash a key / value pair to the session
     * for immediate use.
     */
    public function now(string $key, $value): void;

    /**
     * Reflash all of the session flash data.
     */
    public function reflash(): void;

    /**
     * Reflash a subset of the current flash data.
     *
     * @param array|mixed $keys
     */
    public function keep($keys = null): void;

    /**
     * Add a new Fingerprint generator.
     */
    public function addFingerprintGenerator(Fingerprint $fingerprintGenerator): void;

    /**
     * Get the session handler instance.
     */
    public function getHandler(): SessionHandlerInterface;

    /**
     * Determine if the session handler needs a request.
     */
    public function handlerNeedsRequest(): bool;

    /**
     * Set the request on the handler instance.
     */
    public function setRequestOnHandler(ServerRequestInterface $request): void;

    /**
     * Get used fingerprint.
     */
    public function getFingerprint(): string;

    /**
     * Get the CSRF token value.
     */
    public function getToken(): string;

    /**
     * Set the "previous" URL in the session.
     */
    public function setPreviousUrl(string $url): void;

    /**
     * Get the previous URL from the session.
     */
    public function getPreviousUrl(): ?string;
}
