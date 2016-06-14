<?php
namespace Viserio\Session\Flash;

use Viserio\Contracts\Session\FlashBag as FlashBagContract;

class AutoExpireFlashBag implements FlashBagContract
{
    private $name = 'flashes';

    /**
     * Flash messages.
     *
     * @var array
     */
    private $flashes = [
        'display' => [],
        'new' => []
    ];

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

        // The logic: messages from the last request will be stored in new, so we move them to previous
        // This request we will show what is in 'display'. What is placed into 'new' this time round will
        // be moved to display next time round.
        $this->flashes['display'] = array_key_exists('new', $this->flashes) ? $this->flashes['new'] : [];
        $this->flashes['new'] = [];
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $type, string $message)
    {
        $this->flashes['new'][$type][] = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function peek(string $type, array $default = []): array
    {
        return $this->has($type) ? $this->flashes['display'][$type] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function peekAll(): array
    {
        return array_key_exists('display', $this->flashes) ? (array) $this->flashes['display'] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $type, array $default = []): array
    {
        $return = $default;

        if (!$this->has($type)) {
            return $return;
        }

        if (isset($this->flashes['display'][$type])) {
            $return = $this->flashes['display'][$type];
            unset($this->flashes['display'][$type]);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        $return = $this->flashes['display'];

        $this->flashes = [
            'display' => []
        ];

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function setAll(array $messages)
    {
        $this->flashes['new'] = $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $type, $messages)
    {
        $this->flashes['new'][$type] = is_array($messages) ? $messages : [$messages];
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $type): bool
    {
        return array_key_exists($type, $this->flashes['display']) && $this->flashes['display'][$type];
    }

    /**
     * {@inheritdoc}
     */
    public function keys(): array
    {
        return array_keys($this->flashes['display']);
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
        $return = $this->flashes['display'];

        $this->flashes = [
            'display' => [],
            'new' => []
        ];

        return $return;
    }
}
