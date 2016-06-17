<?php
namespace Viserio\Session;

use DateTimeImmutable;
use RuntimeException;
use Narrowspark\Arr\StaticArr as Arr;
use SessionHandlerInterface as SessionHandlerContract;
use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Contracts\Session\Fingerprint as FingerprintContract;
use Viserio\Contracts\Session\Store as StoreContract;
use Viserio\Support\Str;

class Store implements StoreContract
{
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
     * Specifies the number of seconds after which session will be automatically expired.
     *
     * @var int
     */
    private $ttl = 86400;

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
     * @var Fingerprintcontract[]
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
     * @param string                 $name
     * @param SessionHandlerContract $handler
     * @param EncrypterContract      $encrypter
     */
    public function __construct(string $name, SessionHandlerContract $handler, EncrypterContract $encrypter)
    {
        $this->name = $name;
        $this->handler = $handler;
        $this->encrypter = $encrypter;
    }

    /**
     * {@inheritdoc}
     */
    public function start(): bool
    {
        $this->id = $this->generateSessionId();
        $this->values = [];

        $this->firstTrace = $this->timestamp();
        $this->updateLastTrace();

        $this->requestsCount = 1;
        $this->regenerationTrace = $this->timestamp();

        $this->fingerprint = $this->generateFingerprint();

        $this->started = true;

        return $this->started;
    }

    /**
     * {@inheritdoc}
     */
    public function open(): bool
    {
        if (!$this->started) {
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
    public function setId(string $id)
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

        $this->regenerationTrace = $this->timestamp();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        if ($this->started) {
            if ($this->shouldRegenerateId()) {
                $this->migrate(true);
            }

            $this->updateLastTrace();
            $this->ageFlashData();
            $this->writeToHandler();

            $this->values = [];
            $this->started = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return Arr::has($this->values, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name, $default = null)
    {
        return Arr::get($this->values, $name, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $name, $value)
    {
        $this->values = Arr::set($this->values, $name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function put($key, $value = null)
    {
        if (! is_array($key)) {
            $key = [$key => $value];
        }

        foreach ($key as $arrayKey => $arrayValue) {
            $this->set($arrayKey, $arrayValue);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function push(string $key, $value)
    {
        $array = $this->get($key, []);

        $array[] = $value;

        $this->put($key, $array);
    }

    /**
     * {@inheritdoc}
     */
    public function replace(array $values)
    {
        $this->put($values);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $name)
    {
        $value = $this->get($name);

        $this->values = Arr::forget($this->values, $name);

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->values;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
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
    public function setIdRequestsLimit(int $limit)
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
    public function setIdLiveTime(int $ttl)
    {
        $this->idTtl = $ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function setLiveTime(int $ttl)
    {
        if ($this->started) {
            throw new RuntimeException('Session is already opened, ttl cannot be set.');
        }

        if ($ttl < 1) {
            throw new RuntimeException('$ttl must be greather than 0');
        }

        $this->ttl = $ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function getLiveTime(): int
    {
        return $this->ttl;
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
    public function ageFlashData()
    {
        foreach ($this->get('flash.old', []) as $old) {
            $this->remove($old);
        }

        $this->put('flash.old', $this->get('flash.new', []));

        $this->put('flash.new', []);
    }

    /**
     * {@inheritdoc}
     */
    public function flash(string $key, $value)
    {
        $this->put($key, $value);

        $this->push('flash.new', $key);

        $this->removeFromOldFlashData([$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function now(string $key, $value)
    {
        $this->put($key, $value);

        $this->push('flash.old', $key);
    }

    /**
     * {@inheritdoc}
     */
    public function reflash()
    {
        $this->mergeNewFlashes($this->get('flash.old', []));

        $this->put('flash.old', []);
    }

    /**
     * {@inheritdoc}
     */
    public function keep($keys = null)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $this->mergeNewFlashes($keys);

        $this->removeFromOldFlashData($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function addFingerprintGenerator(FingerprintContract $fingerprintGenerator)
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
    public function getEncrypter(): EncrypterContract
    {
        return $this->encrypter;
    }

    /**
     * {@inheritdoc}
     */
    public function getFingerprint(): string
    {
        return $this->fingerprint;
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
    protected function updateLastTrace()
    {
        $this->lastTrace = $this->timestamp();
    }

    /**
     * Get a new, random session ID.
     *
     * @return string
     */
    protected function generateSessionId(): string
    {
        return hash('sha1', uniqid(Str::random(23), true) . Str::random(25) . microtime(true));
    }

    /**
     * Merge new flash keys into the new flash array.
     *
     * @param array $keys
     */
    private function mergeNewFlashes(array $keys)
    {
        $values = array_unique(array_merge($this->get('flash.new', []), $keys));

        $this->put('flash.new', $values);
    }

    /**
     * Remove the given keys from the old flash data.
     *
     * @param array $keys
     */
    private function removeFromOldFlashData(array $keys)
    {
        $this->put('flash.old', array_diff($this->get('flash.old', []), $keys));
    }

    /**
     * Determine if session id should be regenerated? (based on request_counter or regenerationTrace)
     *
     * @return bool
     */
    private function shouldRegenerateId(): bool
    {
        if ($this->idRequestsLimit !== null && ($this->requestsCount >= $this->idRequestsLimit)) {
            return true;
        }

        if ($this->idTtl && $this->regenerationTrace) {
            return $this->regenerationTrace + $this->idTtl < $this->timestamp();
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

        $this->firstTrace = $metadata['firstTrace'];
        $this->lastTrace = $metadata['lastTrace'];
        $this->regenerationTrace = $metadata['regenerationTrace'];
        $this->requestsCount = $metadata['requestsCount'];
        $this->fingerprint = $metadata['fingerprint'];

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
    private function writeToHandler()
    {
        $values = $this->values;

        $values[self::METADATA_NAMESPACE] = [
            'firstTrace' => $this->firstTrace,
            'lastTrace' => $this->lastTrace,
            'regenerationTrace' => $this->regenerationTrace,
            'requestsCount' => $this->requestsCount,
            'fingerprint' => $this->fingerprint,
        ];

        $this->handler->write(
            $this->id,
            $this->encrypter->encrypt(json_encode($values, \JSON_PRESERVE_ZERO_FRACTION))
        );
        $this->handler->gc($this->ttl);
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
    private function timestamp() : int
    {
        return (new DateTimeImmutable())->getTimestamp();
    }
}
