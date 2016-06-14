<?php
namespace Viserio\Session;

use JsonSerializable;
use Viserio\Contracts\Support\Arrayable;

class MetadataBag implements JsonSerializable, Arrayable
{
     /**
     * @var string
     */
    private $name = '__metadata';

    /**
     * @var string
     */
    private $storageKey;

    /**
     * @var array
     */
    protected $meta = [
        'firstTrace' => 0,
        'lastTrace' => 0,
        'regenerationTrace' => 0,
        'requestsCount' => 0,
        'fingerprint' => 0,
    ];

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
     * @var int
     */
    private $requestsCount;

    /**
     * @var int
     */
    private $regenerationTrace;

    /**
     * Create a new metabag instance.
     *
     * @param string $storageKey      The key used to store bag in the session.
     */
    public function __construct($storageKey = '_v_meta')
    {
        $this->storageKey = $storageKey;
    }

    /**
     * Initializes the Bag.
     *
     * @param array $array
     */
    public function initialize(array &$array)
    {
        $data = &$array;
        $keys = [
            'firstTrace',
            'lastTrace',
            'regenerationTrace',
            'requestsCount',
            'fingerprint',
        ];

        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $this->meta[$key] = $data[$key];
                unset($data[$key]);
            }
        }

        array_merge($this->meta, $data);
    }

    /**
     * Gets first trace timestamp.
     *
     * @return int
     */
    public function getFirstTrace(): int
    {
        return $this->meta['firstTrace'];
    }

    /**
     * Gets last trace timestamp.
     *
     * @return int
     */
    public function getLastTrace(): int
    {
        return $this->meta['lastTrace'];
    }

    /**
     * Gets last (id) regeneration timestamp.
     *
     * @return int
     */
    public function getRegenerationTrace(): int
    {
        return $this->meta['regenerationTrace'];
    }

     /**
     * @return int
     */
    public function getRequestsCount(): int
    {
        return $this->meta['requestsCount'];
    }

    public function getFingerprint()
    {
        return $this->meta['fingerprint'];
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        return $this->meta;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return $this->meta;
    }

    /**
     * Gets the storage key for this bag.
     *
     * @return string
     */
    public function getStorageKey(): string
    {
        return $this->storageKey;
    }
}
