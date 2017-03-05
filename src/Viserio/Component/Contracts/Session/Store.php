<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Session;

use JsonSerializable;
use Psr\Http\Message\ServerRequestInterface;
use SessionHandlerInterface;
use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;

interface Store extends JsonSerializable
{
    public const METADATA_NAMESPACE = '__metadata__';

    /**
     * Starts the session storage.
     * It should be called only once at the beginning. If called for existing
     * session it ovewrites it (clears all values etc).
     *
     * @return bool true if session started
     */
    public function start(): bool;

    /**
     * Opens the session (for a given request).
     *
     * If called earlier, then second (and next ones) call does nothing.
     *
     * @return bool true if session started
     */
    public function open(): bool;

    /**
     * Sets the session ID.
     *
     * @param string $id
     *
     * @return void
     */
    public function setId(string $id);

    /**
     * Returns the session ID.
     *
     * @return string the session ID
     */
    public function getId(): string;

    /**
     * Sets the session name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name);

    /**
     * Returns the session name.
     *
     * @return string the session name
     */
    public function getName();

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
     *
     * @return void
     */
    public function save();

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
     *
     * @return mixed
     */
    public function get(string $name, $default = null);

    /**
     * Sets an attribute.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function set(string $name, $value);

    /**
     * Push a value onto a session array.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function push(string $key, $value);

    /**
     * Removes an attribute.
     *
     * @param string $name
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
     *
     * @return void
     */
    public function clear();

    /**
     * Checks if the session was started.
     *
     * @return bool
     */
    public function isStarted(): bool;

    /**
     * Set the request limit for a session.
     *
     * @param int $limit
     *
     * @return void
     */
    public function setIdRequestsLimit(int $limit);

    /**
     * Shows the counted request for session.
     *
     * @return int
     */
    public function getRequestsCount(): int;

    /**
     * Specifies the number of seconds after which session
     * will be automatically expired.
     *
     * @param int $ttl
     *
     * @return void
     */
    public function setIdLiveTime(int $ttl);

    /**
     * Gets last trace timestamp.
     *
     * @return int
     */
    public function getLastTrace(): int;

    /**
     * Gets first trace timestamp.
     *
     * @return int
     */
    public function getFirstTrace(): int;

    /**
     * Gets last (id) regeneration timestamp.
     *
     * @return int
     */
    public function getRegenerationTrace(): int;

    /**
     * Age the flash data for the session.
     *
     * @return void
     */
    public function ageFlashData(): void;

    /**
     * Flash a key / value pair to the session.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function flash(string $key, $value): void;

    /**
     * Flash a key / value pair to the session
     * for immediate use.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function now(string $key, $value): void;

    /**
     * Reflash all of the session flash data.
     *
     * @return void
     */
    public function reflash(): void;

    /**
     * Reflash a subset of the current flash data.
     *
     * @param array|mixed $keys
     *
     * @return void
     */
    public function keep($keys = null): void;

    /**
     * Add a new Fingerprint generator.
     *
     * @param Fingerprint $fingerprintGenerator
     *
     * @return void
     */
    public function addFingerprintGenerator(Fingerprint $fingerprintGenerator);

    /**
     * Get the session handler instance.
     *
     * @return \SessionHandlerInterface
     */
    public function getHandler(): SessionHandlerInterface;

    /**
     * Determine if the session handler needs a request.
     *
     * @return bool
     */
    public function handlerNeedsRequest(): bool;

    /**
     * Set the request on the handler instance.
     *
     * @param ServerRequestInterface $request
     *
     * @return void
     */
    public function setRequestOnHandler(ServerRequestInterface $request);

    /**
     * Get the encrypter instance.
     *
     * @return EncrypterContract
     */
    public function getEncrypter(): EncrypterContract;

    /**
     * Get used fingerprint.
     *
     * @return string
     */
    public function getFingerprint(): string;

    /**
     * Get the CSRF token value.
     *
     * @return string
     */
    public function getToken(): string;

    /**
     * Set the "previous" URL in the session.
     *
     * @param string $url
     *
     * @return void
     */
    public function setPreviousUrl(string $url): void;

    /**
     * Get the previous URL from the session.
     *
     * @return string|null
     */
    public function getPreviousUrl(): ?string;
}
