<?php
namespace Viserio\Session;

use InvalidArgumentException;
use Narrowspark\Arr\StaticArr as Arr;
use Viserio\Contracts\Session\SessionHandler as SessionHandlerContract;
use Viserio\Contracts\Session\Store as StoreContract;

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
     * The session attributes.
     *
     * @var array
     */
    protected $attributes = [];


    /**
     * The session handler implementation.
     *
     * @var \SessionHandlerInterface
     */
    protected $handler;

    /**
     * Session store started status.
     *
     * @var bool
     */
    protected $started = false;

    /**
     * Create a new session instance.
     *
     * @param string                   $name
     * @param \SessionHandlerInterface $handler
     * @param string|null              $id
     */
    public function __construct(string $name, SessionHandlerContract $handler, $id = null)
    {
        $this->setId($id);
        $this->name = $name;
        $this->handler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        $this->loadSession();

        if (! $this->has('_token')) {
            $this->regenerateToken();
        }

        return $this->started = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        if (! $this->isValidId($id)) {
            $id = $this->generateSessionId();
        }

        $this->id = $id;
    }

    /**
     * Determine if this is a valid session ID.
     *
     * @param string $id
     *
     * @return bool
     */
    public function isValidId($id)
    {
        return is_string($id) && preg_match('/^[a-f0-9]{40}$/', $id);
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
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function invalidate($lifetime = null)
    {
        $this->attributes = [];
        $this->migrate();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function migrate($destroy = false, $lifetime = null)
    {
        if ($destroy) {
            $this->handler->destroy($this->getId());
        }

        $this->setExists(false);
        $this->id = $this->generateSessionId();

        return true;
    }

    /**
     * Generate a new session identifier.
     *
     * @param bool $destroy
     *
     * @return bool
     */
    public function regenerate($destroy = false)
    {
        return $this->migrate($destroy);
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $this->ageFlashData();
        $this->handler->write($this->getId(), serialize($this->attributes));
        $this->started = false;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return $this->get($name) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, $default = null)
    {
        return Arr::get($this->attributes, $name, $default);
    }

    /**
     * Get the value of a given key and then forget it.
     *
     * @param string $key
     * @param string $default
     *
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return Arr::pull($this->attributes, $key, $default);
    }

    /**
     * Determine if the session contains old input.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasOldInput($key = null)
    {
        $old = $this->getOldInput($key);

        return $key === null ? count($old) > 0 : $old !== null;
    }

    /**
     * Get the requested item from the flashed input array.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getOldInput($key = null, $default = null)
    {
        $input = $this->get('_old_input', []);

        // Input that is flashed to the session can be easily retrieved by the
        // developer, making repopulating old forms and the like much more
        // convenient, since the request's previous input is available.
        return Arr::get($input, $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        array_set($this->attributes, $name, $value);
    }

    /**
     * Put a key / value pair or array of key / value pairs in the session.
     *
     * @param string|array $key
     * @param mixed|null   $value
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
     * Push a value onto a session array.
     *
     * @param string $key
     * @param string $value
     */
    public function push($key, $value)
    {
        $array = $this->get($key, []);
        $array[] = $value;
        $this->put($key, $array);
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function replace(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->put($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($name)
    {
        return Arr::pull($this->attributes, $name);
    }

    /**
     * Remove an item from the session.
     *
     * @param string $key
     */
    public function forget($key)
    {
        Arr::forget($this->attributes, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->attributes = [];

        foreach ($this->bags as $bag) {
            $bag->clear();
        }
    }

    /**
     * Remove all of the items from the session.
     */
    public function flush()
    {
        $this->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * Get the CSRF token value.
     *
     * @return string
     */
    public function token()
    {
        return $this->get('_token');
    }

    /**
     * Get the CSRF token value.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token();
    }

    /**
     * Regenerate the CSRF token value.
     */
    public function regenerateToken()
    {
        $this->put('_token', str_random(40));
    }

    /**
     * Get the underlying session handler implementation.
     *
     * @return \SessionHandlerInterface
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Load the session data from the handler.
     */
    protected function loadSession()
    {
        $this->attributes = $this->readFromHandler();

        foreach (array_merge($this->bags, [$this->metaBag]) as $bag) {
            $this->initializeLocalBag($bag);
            $bag->initialize($this->bagData[$bag->getStorageKey()]);
        }
    }

    /**
     * Read the session data from the handler.
     *
     * @return array
     */
    protected function readFromHandler()
    {
        $data = $this->handler->read($this->getId());

        return $data ? unserialize($data) : [];
    }

    /**
     * Initialize a bag in storage if it doesn't exist.
     *
     * @param \Symfony\Component\HttpFoundation\Session\SessionBagInterface $bag
     */
    protected function initializeLocalBag($bag)
    {
        $this->bagData[$bag->getStorageKey()] = $this->pull($bag->getStorageKey(), []);
    }

    /**
     * Get a new, random session ID.
     *
     * @return string
     */
    protected function generateSessionId()
    {
        return sha1(uniqid('', true) . str_random(25) . microtime(true));
    }
}
