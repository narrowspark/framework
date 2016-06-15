<?php
namespace Viserio\Session;

use RuntimeException;
use Narrowspark\Arr\StaticArr as Arr;
use ParagonIE\ConstantTime\Binary;
use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Contracts\Session\SessionHandler as SessionHandlerContract;
use Viserio\Contracts\Session\Fingerprint as FingerprintContract;
use Viserio\Contracts\Session\Store as StoreContract;
use Viserio\Contracts\Support\CharacterType;

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
     * @var integer
     */
    private $idTtl = 1440;

    /**
     * Last (id) regeneration timestamp.
     *
     * @var int
     */
    private $regenerationTrace = 0;

    /**
     * Specifies the number of seconds after which session will be automatically expired.
     *
     * @var int
     */
    private $ttl = 1440;

    /**
     * First trace (timestamp), time when session was created.
     *
     * @var int
     */
    private $firstTrace = 0;

    /**
     * Last trace (Unix timestamp).
     *
     * @var int
     */
    private $lastTrace = 0;

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
     * The session attributes.
     *
     * @var array
     */
    private $attributes = [];

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
        $this->attributes = [];

        $this->firstTrace = time();
        $this->updateLastTrace();

        $this->requestsCount = 1;
        $this->regenerationTrace = time();

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

        $this->regenerationTrace = time();

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
                $this->regenerateId();
            }

            $this->updateLastTrace();
            $this->writeToHandler();

            $this->attributes = [];
            $this->started = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return Arr::has($this->attributes, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name, $default = null)
    {
        return Arr::get($this->attributes, $name, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $name, $value)
    {
        $this->attributes = Arr::set($this->attributes, $name, $value);
    }

    /**
     * Put a key / value pair or array of key / value pairs in the session.
     *
     * @param  string|array $key
     * @param  mixed        $value
     *
     * @return void
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
    public function replace(array $attributes)
    {
        $this->put($attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $name)
    {
        $value = $this->get($name);

        $this->attributes = Arr::forget($this->attributes, $name);

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->attributes = [];
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * @param int $limit
     */
    public function setIdRequestsLimit($limit)
    {
        $this->idRequestsLimit = $limit;
    }

    /**
     * @param int $ttl
     */
    public function setIdTtl($ttl)
    {
        $this->idTtl = $ttl;
    }

    /**
     * It must be called before {@link self::start()}.
     *
     * @param int $ttl
     *
     * @throws Exception
     */
    public function setTtl(int $ttl)
    {
        if ($this->isStarted) {
            throw new RuntimeException('Session is already opened, ttl cannot be set');
        }

        if ($ttl < 1) {
            throw new RuntimeException('$ttl must be greather than 0');
        }

        $this->ttl = $ttl;
    }

    /**
     * @return int
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * Gets last trace timestamp.
     *
     * @return int
     */
    public function getLastTrace(): int
    {
        return $this->lastTrace;
    }

    /**
     * Gets first trace timestamp.
     *
     * @return int
     */
    public function getFirstTrace(): int
    {
        return $this->firstTrace;
    }

    /**
     * Gets last (id) regeneration timestamp.
     *
     * @return int
     */
    public function getRegenerationTrace(): int
    {
        return $this->regenerationTrace;
    }

    /**
     * @return int
     */
    public function getRequestsCount(): int
    {
        return $this->requestsCount;
    }

    /**
     * @param FingerprintContract $fingerprintGenerator
     */
    public function addFingerprintGenerator(FingerprintContract $fingerprintGenerator)
    {
        $this->fingerprintGenerators[] = $fingerprintGenerator;
    }

    /**
     * Get the underlying session handler implementation.
     *
     * @return SessionHandlerContract
     */
    public function getHandler(): SessionHandlerContract
    {
        return $this->handler;
    }

    /**
     * Get the underlying session handler implementation.
     *
     * @return SessionHandlerContract
     */
    public function getEncrypter(): EncrypterContract
    {
        return $this->encrypter;
    }

    /**
     * Get used fingerprint.
     *
     * @return string
     */
    public function getFingerprint(): string
    {
        return $this->fingerprint;
    }

    /**
     * Determine if this is a valid session ID.
     *
     * @param  string  $id
     * @return bool
     */
    protected function isValidId($id)
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
        $this->lastTrace = time();
    }

    /**
     * Get a new, random session ID.
     *
     * @return string
     */
    protected function generateSessionId()
    {
        return hash('sha1', uniqid(self::random(23), true) . self::random(25) . microtime(true));
    }

    /**
     * Determine if session id should be regenerated? (based on request_counter or regenerationTrace)
     *
     * @return bool
     */
    private function shouldRegenerateId(): bool
    {
        if (($this->idRequestsLimit) && ($this->requestsCount >= $this->idRequestsLimit)) {
            return true;
        }

        if (($this->idTtl && $this->regenerationTrace) && ($this->regenerationTrace + $this->idTtl < time())) {
            return true;
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

        $this->attributes = array_merge($this->attributes, $values);

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
     * Write attributes to handler.
     *
     * @return void
     */
    private function writeToHandler()
    {
        $values = $this->attributes;

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
     * Generate a random string of a given length and character set
     *
     * @param int $length How many characters do you want?
     *
     * @return string
     */
    private static function random(int $length = 64): string
    {
        $str = '';
        $characters = CharacterType::PRINTABLE_ASCII;
        $l = Binary::safeStrlen($characters) - 1;

        for ($i = 0; $i < $length; ++$i) {
            $r = random_int(0, $l);
            $str .= $characters[$r];
        }

        return $str;
    }
}
