<?php
namespace Viserio\Session;

use Exception;
use RuntimeException;
use Narrowspark\Arr\StaticArr as Arr;
use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Contracts\Session\SessionHandler as SessionHandlerContract;
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
     * The meta-data bag instance.
     *
     * @var MetadataBag
     */
    protected $metaBag;

    /**
     * The session bags.
     *
     * @var array
     */
    protected $bags = [];

    /**
     * Local copies of the session bag data.
     *
     * @var array
     */
    protected $bagData = [];

    /**
     * The session attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Create a new session instance.
     *
     * @param string                 $name
     * @param SessionHandlerContract $handler
     * @param EncrypterContract      $encrypter
     * @param string|null            $id
     */
    public function __construct(string $name, SessionHandlerContract $handler, EncrypterContract $encrypter)
    {
        $this->name = $name;
        $this->handler = $handler;
        $this->metabag = new MetadataBag();
    }

    /**
     * {@inheritdoc}
     */
    public function start(): bool
    {
        if (!$this->started) {
            $this->id = $this->generateSessionId();

            $this->loadSession();

            $this->started = true;
        }

        return $this->started;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name)
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $bag = $this->metabag->initialize($this->attributes);

        $this->handler->write($this->id, $bag, $this->ttl);

        $this->started = false;
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
        return Arr::set($this->attributes, $name, $value);
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
    public function all(): array
    {
        return $this->attributes;
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
        return Arr::pull($this->attributes, $name);
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
     * Gets last (id) regeneration timestamp.
     *
     * @return int
     */
    public function getRegenerationTrace()
    {
        return $this->regenerationTrace;
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
            throw new Exception('$ttl must be greather than 0');
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
     * @return int
     */
    public function getRequestsCount()
    {
        return $this->requestsCount;
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
     * {@inheritdoc}
     */
    public function getMetadataBag(): MetadataBag
    {
        return $this->metaBag;
    }

    /**
     * Get a new, random session ID.
     *
     * @return string
     */
    protected function generateSessionId()
    {
        return hash('sha1', uniqid(Str::random(23), true) . Str::random(25) . microtime(true));
    }

    /**
     * Determine if session id should be regenerated? (based on request_counter or regenerationTrace)
     *
     * @return bool
     */
    protected function shouldRegenerateId(): bool
    {
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
     * Load the session data from the handler.
     *
     * @return bool
     */
    protected function loadSession(): bool
    {
        $bag = $this->handler->read($this->id);

        if (!$bag) {
            return false;
        }

        $this->firstTrace = $bag->getFirstTrace();
        $this->lastTrace = $bag->getLastTrace();
        $this->regenerationTrace = $bag->getRegenerationTrace();
        $this->requestsCount = $bag->getRequestsCount();
        $this->fingerprint = $bag->getFingerprint();

        $this->attributes = $bag->toArray();

        return true;
    }
}
