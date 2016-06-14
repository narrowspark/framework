<?php
namespace Viserio\Session\Flash;

use Viserio\Contracts\Session\FlashBag as FlashBagContract;

class FlashBag implements FlashBagContract
{
    private $name = 'flashes';

    /**
     * Flash messages.
     *
     * @var array
     */
    private $flashes = [];

    /**
     * The storage key for flashes in the session.
     *
     * @var string
     */
    private $storageKey;
    /**
     * Constructor.
     *
     * @param string $storageKey The key used to store flashes in the session.
     */
    public function __construct(string $storageKey = '_v_flashes')
    {
        $this->storageKey = $storageKey;
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
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array &$flashes)
    {
        $this->flashes = &$flashes;
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $type, string $message)
    {
        $this->flashes[$type][] = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function peek(string $type, array $default = []): array
    {
        return $this->has($type) ? $this->flashes[$type] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function peekAll(): array
    {
        return $this->flashes;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $type, array $default = []): array
    {
        if (!$this->has($type)) {
            return $default;
        }

        $return = $this->flashes[$type];
        unset($this->flashes[$type]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        $return = $this->peekAll();
        $this->flashes = [];

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $type, $messages)
    {
        $this->flashes[$type] = is_array($messages) ? $messages : [$messages];
    }

    /**
     * {@inheritdoc}
     */
    public function setAll(array $messages)
    {
        $this->flashes = $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $type): bool
    {
        return array_key_exists($type, $this->flashes) && $this->flashes[$type];
    }

    /**
     * {@inheritdoc}
     */
    public function keys(): array
    {
        return array_keys($this->flashes);
    }

    /**
     * {@inheritdoc}
     */
    public function getStorageKey(): string
    {
        return $this->storageKey;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): array
    {
        return $this->all();
    }
}
