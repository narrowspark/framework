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

namespace Viserio\Component\Session;

use Cake\Chronos\Chronos;
use Psr\Http\Message\ServerRequestInterface;
use SessionHandlerInterface as SessionHandlerContract;
use Viserio\Component\Session\Handler\CookieSessionHandler;
use Viserio\Component\Support\Str;
use Viserio\Contract\Session\Exception\InvalidArgumentException;
use Viserio\Contract\Session\Exception\SessionNotStartedException;
use Viserio\Contract\Session\Exception\SuspiciousOperationException;
use Viserio\Contract\Session\Fingerprint as FingerprintContract;
use Viserio\Contract\Session\Store as StoreContract;

class Store implements StoreContract
{
    /**
     * The session ID.
     *
     * @var null|string
     */
    protected $id;

    /**
     * The session name.
     *
     * @var string
     */
    protected $name;

    /**
     * Session store started status.
     *
     * @var bool
     */
    protected $started = false;

    /**
     * The session handler implementation.
     *
     * @var SessionHandlerContract
     */
    protected $handler;

    /**
     * Number of requests after which id is regenerated.
     *
     * @var null|int
     */
    private $idRequestsLimit;

    /**
     * The number of seconds the session should be valid.
     *
     * @var int
     */
    private $idTtl = 86400;

    /**
     * Last (id) regeneration (Unix timestamp).
     *
     * @var null|int
     */
    private $regenerationTrace;

    /**
     * First trace (Unix timestamp), time when session was created.
     *
     * @var null|int
     */
    private $firstTrace;

    /**
     * Last trace (Unix timestamp).
     *
     * @var null|int
     */
    private $lastTrace;

    /**
     * Counted requests.
     *
     * @var int
     */
    private $requestsCount = 0;

    /**
     * All fingerprint generators.
     *
     * @var array
     */
    private $fingerprintGenerators = [];

    /**
     * Full fingerprint.
     *
     * @var string
     */
    private $fingerprint = '';

    /**
     * The session values.
     *
     * @var array
     */
    private $values = [];

    /**
     * Create a new session instance.
     *
     * @param string                   $name
     * @param \SessionHandlerInterface $handler
     */
    public function __construct(string $name, SessionHandlerContract $handler)
    {
        $this->setName($name);
        $this->handler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId(string $id): void
    {
        if (! $this->isValidId($id)) {
            $id = $this->generateSessionId();
        }

        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name): void
    {
        \parse_str($name, $parsed);

        if (\implode('&', \array_keys($parsed)) !== $name) {
            throw new InvalidArgumentException(\sprintf('Session name [%s] contains illegal character(s).', $name));
        }

        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * {@inheritdoc}
     */
    public function getHandler(): SessionHandlerContract
    {
        return $this->handler;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdRequestsLimit(int $limit): void
    {
        $this->idRequestsLimit = $limit;
    }

    /**
     * {@inheritdoc}
     */
    public function getRegenerationTrace(): ?int
    {
        return $this->regenerationTrace;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstTrace(): ?int
    {
        return $this->firstTrace;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastTrace(): ?int
    {
        return $this->lastTrace;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestsCount(): int
    {
        return $this->requestsCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getFingerprint(): string
    {
        return $this->fingerprint;
    }

    /**
     * {@inheritdoc}
     */
    public function start(): bool
    {
        $this->started = true;

        $this->id = $this->generateSessionId();
        $this->values = [];

        $this->firstTrace = $this->getTimestamp();
        $this->updateLastTrace();

        $this->requestsCount = 1;
        $this->regenerationTrace = $this->getTimestamp();

        $this->fingerprint = $this->generateFingerprint();

        if (! $this->has('_token')) {
            $this->regenerateToken();
        }

        return $this->started;
    }

    /**
     * {@inheritdoc}
     */
    public function open(): bool
    {
        if (! $this->started) {
            if ($this->getId() !== null) {
                $this->loadSession();

                if ($this->getFirstTrace() === null) {
                    return false;
                }

                if ($this->generateFingerprint() !== $this->getFingerprint()) {
                    throw new SuspiciousOperationException();
                }

                $this->started = true;
                $this->requestsCount++;
            }
        }

        return $this->started;
    }

    /**
     * {@inheritdoc}
     */
    public function isExpired(): bool
    {
        $lastTrace = $this->getLastTrace();

        if ($lastTrace === null) {
            return true;
        }

        return $lastTrace + $this->getTtl() < \time();
    }

    /**
     * {@inheritdoc}
     */
    public function invalidate(): bool
    {
        $this->clear();

        return $this->migrate(true);
    }

    /**
     * {@inheritdoc}
     */
    public function migrate(bool $destroy = false): bool
    {
        if ($destroy) {
            $this->handler->destroy($this->id);
        }

        $this->id = $this->generateSessionId();
        $this->regenerationTrace = $this->getTimestamp();
        $this->requestsCount = 0;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(): void
    {
        if (! $this->started) {
            return;
        }

        if ($this->shouldRegenerateId()) {
            $this->migrate(true);
        }

        $this->updateLastTrace();
        $this->ageFlashData();
        $this->writeToHandler();

        $this->values = [];
        $this->started = false;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        $this->checkIfSessionHasStarted();

        return isset($this->values[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name, $default = null)
    {
        $this->checkIfSessionHasStarted();

        return $this->has($name) ? $this->values[$name] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $name, $value): void
    {
        $this->checkIfSessionHasStarted();

        $this->values[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function push(string $key, $value): void
    {
        $array = $this->get($key, []);

        $array[] = $value;

        $this->set($key, $array);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $name)
    {
        $value = $this->get($name);

        if ($this->has($name)) {
            unset($this->values[$name]);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        return $this->values;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->values = [];
    }

    /**
     * {@inheritdoc}
     */
    public function setIdLiveTime(int $ttl): void
    {
        $this->idTtl = $ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function getTtl(): int
    {
        return $this->idTtl;
    }

    /**
     * {@inheritdoc}
     */
    public function ageFlashData(): void
    {
        foreach ($this->get('_flash.old', []) as $old) {
            $this->remove($old);
        }

        $this->set('_flash.old', $this->get('_flash.new', []));

        $this->set('_flash.new', []);
    }

    /**
     * {@inheritdoc}
     */
    public function flash(string $key, $value): void
    {
        $this->set($key, $value);

        $this->push('_flash.new', $key);

        $this->removeFromOldFlashData([$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function now(string $key, $value): void
    {
        $this->set($key, $value);

        $this->push('_flash.old', $key);
    }

    /**
     * {@inheritdoc}
     */
    public function reflash(): void
    {
        $this->mergeNewFlashes($this->get('_flash.old', []));

        $this->set('_flash.old', []);
    }

    /**
     * {@inheritdoc}
     */
    public function keep($keys = null): void
    {
        $keys = \is_array($keys) ? $keys : \func_get_args();

        $this->mergeNewFlashes($keys);

        $this->removeFromOldFlashData($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function addFingerprintGenerator(FingerprintContract $fingerprintGenerator): void
    {
        $this->fingerprintGenerators[] = $fingerprintGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function handlerNeedsRequest(): bool
    {
        return $this->handler instanceof CookieSessionHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestOnHandler(ServerRequestInterface $request): void
    {
        if ($this->handlerNeedsRequest()) {
            $this->handler->setRequest($request);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return $this->values;
    }

    /**
     * Get the CSRF token value.
     *
     * @return string
     */
    public function getToken(): string
    {
        $token = $this->get('_token');

        if (\is_string($token)) {
            return $token;
        }

        return '';
    }

    /**
     * Regenerate the CSRF token value.
     *
     * @return void
     */
    public function regenerateToken(): void
    {
        $this->set('_token', \bin2hex(Str::random(40)));
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviousUrl(): ?string
    {
        return $this->get('_previous.url');
    }

    /**
     * {@inheritdoc}
     */
    public function setPreviousUrl(string $url): void
    {
        $this->set('_previous.url', $url);
    }

    /**
     * Determine if this is a valid session ID.
     *
     * @param string $id
     *
     * @return bool
     */
    protected function isValidId($id): bool
    {
        return \is_string($id) && \preg_match('/^[a-f0-9]{40}$/', $id) === 1;
    }

    /**
     * Updates last trace timestamp.
     *
     * @return void
     */
    protected function updateLastTrace(): void
    {
        $this->lastTrace = $this->getTimestamp();
    }

    /**
     * Get a new, random session ID.
     *
     * @return string
     */
    protected function generateSessionId(): string
    {
        return \hash('ripemd160', \uniqid(Str::random(23), true) . Str::random(25) . \microtime(true));
    }

    /**
     * Check if session has already started.
     *
     * @throws \Viserio\Contract\Session\Exception\SessionNotStartedException
     *
     * @return void
     */
    protected function checkIfSessionHasStarted(): void
    {
        if (! $this->isStarted()) {
            throw new SessionNotStartedException('The session is not started.');
        }
    }

    /**
     * Prepare the raw string data from the session.
     *
     * @param string $data
     *
     * @return array
     */
    protected function prepareForReadFromHandler($data): array
    {
        $value = \json_decode($data, true);

        if ($value === null) {
            return [];
        }

        return $value;
    }

    /**
     * Prepare the session data for storage.
     *
     * @param string $data
     *
     * @return string
     */
    protected function prepareForWriteToHandler(string $data): string
    {
        return $data;
    }

    /**
     * Merge new flash keys into the new flash array.
     *
     * @param array $keys
     *
     * @return void
     */
    private function mergeNewFlashes(array $keys): void
    {
        $values = \array_unique(\array_merge($this->get('_flash.new', []), $keys));

        $this->set('_flash.new', $values);
    }

    /**
     * Remove the given keys from the old flash data.
     *
     * @param array $keys
     *
     * @return void
     */
    private function removeFromOldFlashData(array $keys): void
    {
        $this->set('_flash.old', \array_diff($this->get('_flash.old', []), $keys));
    }

    /**
     * Determine if session id should be regenerated? (based on requestsCount or regenerationTrace).
     *
     * @return bool
     */
    private function shouldRegenerateId(): bool
    {
        if ($this->idRequestsLimit !== null && ($this->requestsCount >= $this->idRequestsLimit)) {
            return true;
        }

        if ($this->regenerationTrace !== 0) {
            $expires = Chronos::createFromTimestamp($this->regenerationTrace)->addSeconds($this->getTtl())->getTimestamp();

            return $expires < $this->getTimestamp();
        }

        return false;
    }

    /**
     * Load the session data from the handler.
     *
     * @return bool
     */
    private function loadSession(): bool
    {
        $values = $this->readFromHandler();

        if (\count($values) === 0) {
            return false;
        }

        $metadata = $values[self::METADATA_NAMESPACE];

        $this->firstTrace = $metadata['firstTrace'];
        $this->lastTrace = $metadata['lastTrace'];
        $this->regenerationTrace = $metadata['regenerationTrace'];
        $this->requestsCount = $metadata['requestsCount'];
        $this->fingerprint = $metadata['fingerprint'];

        $this->values = \array_merge($this->values, $values);

        return true;
    }

    /**
     * Read the session data from the handler.
     *
     * @return array
     */
    private function readFromHandler(): array
    {
        $data = $this->handler->read($this->id);

        if ($data === '') {
            return [];
        }

        return $this->prepareForReadFromHandler($data);
    }

    /**
     * Write values to handler.
     *
     * @return void
     */
    private function writeToHandler(): void
    {
        $values = $this->values;

        $values[self::METADATA_NAMESPACE] = [
            'firstTrace' => $this->firstTrace,
            'lastTrace' => $this->lastTrace,
            'regenerationTrace' => $this->regenerationTrace,
            'requestsCount' => $this->requestsCount,
            'fingerprint' => $this->fingerprint,
        ];

        $value = \json_encode($values, \JSON_PRESERVE_ZERO_FRACTION);

        $this->handler->write(
            $this->id,
            $this->prepareForWriteToHandler($value)
        );
    }

    /**
     * Generate a fingerprint string.
     *
     * @return string
     */
    private function generateFingerprint(): string
    {
        $fingerprint = '';

        foreach ($this->fingerprintGenerators as $fingerprintGenerator) {
            $fingerprint .= $fingerprintGenerator->generate();
        }

        return $fingerprint;
    }

    /**
     * Get time stamp.
     *
     * @return int
     */
    private function getTimestamp(): int
    {
        return Chronos::now()->toMutable()->getTimestamp();
    }
}
