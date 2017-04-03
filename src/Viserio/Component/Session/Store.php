<?php
declare(strict_types=1);
namespace Viserio\Component\Session;

use DateTimeImmutable;
use Narrowspark\Arr\Arr;
use Psr\Http\Message\ServerRequestInterface;
use SessionHandlerInterface as SessionHandlerContract;
use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Contracts\Encryption\Traits\EncrypterAwareTrait;
use Viserio\Component\Contracts\Session\Exceptions\SessionNotStartedException;
use Viserio\Component\Contracts\Session\Fingerprint as FingerprintContract;
use Viserio\Component\Contracts\Session\Store as StoreContract;
use Viserio\Component\Session\Handler\CookieSessionHandler;
use Viserio\Component\Support\Str;

class Store implements StoreContract
{
    use EncrypterAwareTrait;

    /**
     * The session ID.
     *
     * @var string
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
     * Encrypter instance.
     *
     * @var EncrypterContract
     */
    protected $encrypter;

    /**
     * Number of requests after which id is regeneratd.
     *
     * @var int|null
     */
    private $idRequestsLimit = null;

    /**
     * Time after id is regenerated.
     *
     * @var int
     */
    private $idTtl = 86400;

    /**
     * Last (id) regeneration timestamp.
     *
     * @var int
     */
    private $regenerationTrace;

    /**
     * First trace (timestamp), time when session was created.
     *
     * @var int
     */
    private $firstTrace;

    /**
     * Last trace (Unix timestamp).
     *
     * @var int
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
     * @param string                                            $name
     * @param \SessionHandlerInterface                          $handler
     * @param \Viserio\Component\Contracts\Encryption\Encrypter $encrypter
     */
    public function __construct(string $name, SessionHandlerContract $handler, EncrypterContract $encrypter)
    {
        $this->name      = $name;
        $this->handler   = $handler;
        $this->encrypter = $encrypter;
    }

    /**
     * {@inheritdoc}
     */
    public function start(): bool
    {
        $this->started = true;

        $this->id     = $this->generateSessionId();
        $this->values = [];

        $this->firstTrace = $this->getTimestamp();
        $this->updateLastTrace();

        $this->requestsCount     = 1;
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
            if ($this->id) {
                $this->loadSession();

                $this->started = true;
                $this->requestsCount += 1;
            }
        }

        return $this->started;
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
    public function getId(): string
    {
        return $this->id;
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

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name): void
    {
        $this->name = $name;
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
    public function save(): void
    {
        if ($this->started) {
            if ($this->shouldRegenerateId()) {
                $this->migrate(true);
            }

            $this->updateLastTrace();
            $this->ageFlashData();
            $this->writeToHandler();

            $this->values  = [];
            $this->started = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        $this->checkIfSessionHasStarted();

        return Arr::has($this->values, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name, $default = null)
    {
        $this->checkIfSessionHasStarted();

        return Arr::get($this->values, $name, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $name, $value): void
    {
        $this->checkIfSessionHasStarted();

        $this->values = Arr::set($this->values, $name, $value);
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

        Arr::forget($this->values, $name);

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
    public function isStarted(): bool
    {
        return $this->started;
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
    public function getRequestsCount(): int
    {
        return $this->requestsCount;
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
    public function getLastTrace(): int
    {
        return $this->lastTrace;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstTrace(): int
    {
        return $this->firstTrace;
    }

    /**
     * {@inheritdoc}
     */
    public function getRegenerationTrace(): int
    {
        return $this->regenerationTrace;
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
        $keys = is_array($keys) ? $keys : func_get_args();

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
    public function getHandler(): SessionHandlerContract
    {
        return $this->handler;
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
    public function setRequestOnHandler(ServerRequestInterface $request)
    {
        if ($this->handlerNeedsRequest()) {
            $this->handler->setRequest($request);
        }
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
     *
     * @codeCoverageIgnore
     */
    public function jsonSerialize()
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
        return $this->get('_token');
    }

    /**
     * Regenerate the CSRF token value.
     *
     * @return void
     */
    public function regenerateToken(): void
    {
        $this->set('_token', bin2hex(Str::random(40)));
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
        return is_string($id) && preg_match('/^[a-f0-9]{40}$/', $id);
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
        return hash('ripemd160', uniqid(Str::random(23), true) . Str::random(25) . microtime(true));
    }

    /**
     * Check if session has already started.
     *
     * @throws \Viserio\Component\Contracts\Session\Exceptions\SessionNotStartedException
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
     * Merge new flash keys into the new flash array.
     *
     * @param array $keys
     *
     * @return void
     */
    private function mergeNewFlashes(array $keys): void
    {
        $values = array_unique(array_merge($this->get('_flash.new', []), $keys));

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
        $this->set('_flash.old', array_diff($this->get('_flash.old', []), $keys));
    }

    /**
     * Determine if session id should be regenerated? (based on request_counter or regenerationTrace).
     *
     * @return bool
     */
    private function shouldRegenerateId(): bool
    {
        if ($this->idRequestsLimit !== null && ($this->requestsCount >= $this->idRequestsLimit)) {
            return true;
        }

        if ($this->idTtl && $this->regenerationTrace) {
            return $this->regenerationTrace + $this->idTtl < $this->getTimestamp();
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

        if (empty($values)) {
            return false;
        }

        $metadata = $values[self::METADATA_NAMESPACE];

        $this->firstTrace        = $metadata['firstTrace'];
        $this->lastTrace         = $metadata['lastTrace'];
        $this->regenerationTrace = $metadata['regenerationTrace'];
        $this->requestsCount     = $metadata['requestsCount'];
        $this->fingerprint       = $metadata['fingerprint'];

        $this->values = array_merge($this->values, $values);

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

        if ($data) {
            return json_decode($this->encrypter->decrypt($data), true);
        }

        return [];
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
            'firstTrace'        => $this->firstTrace,
            'lastTrace'         => $this->lastTrace,
            'regenerationTrace' => $this->regenerationTrace,
            'requestsCount'     => $this->requestsCount,
            'fingerprint'       => $this->fingerprint,
        ];

        $this->handler->write(
            $this->id,
            $this->encrypter->encrypt(json_encode($values, \JSON_PRESERVE_ZERO_FRACTION))
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
        return (new DateTimeImmutable())->getTimestamp();
    }
}
