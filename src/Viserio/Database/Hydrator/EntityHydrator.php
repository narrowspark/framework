<?php
declare(strict_types=1);
namespace Viserio\Database\Hydrator;

use Doctrine\DBAL\Driver\Statement;
use Viserio\Contracts\Database\Hydrator as HydratorContract;

class EntityHydrator implements HydratorContract
{
    /**
     * The [type] instance.
     *
     * @var [type]
     */
    protected $provider;

    /**
     * Create a new hydrator instance.
     *
     * @param [type] $provider
     */
    public function __construct($provider)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function extract(): array
    {

    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(array $data = [], $option = self::HYDRATE_AUTO)
    {
        $option = $this->determineOption($data, $option);

    }

    /**
     * Determine a option from the data or option setting.
     *
     * @param array  $data
     * @param string $option
     *
     * @return string
     */
    protected function determineOption(array $data, string $option): string
    {
        if ($option === self::HYDRATE_RAW) {
            return self::HYDRATE_RAW;
        }

        if ($option === self::HYDRATE_AUTO) {
            return $this->isCollectable($data) && count($data) > 0 ? self::HYDRATE_COLLECTION : self::HYDRATE_ENTITY;
        }

        return $option;
    }

    /**
     * Check if data are collectable.
     *
     * @param mixed $data
     *
     * @return bool
     */
    protected function isCollectable($data): bool
    {
        if (! is_array($data)) {
            return false;
        }

        return is_array(reset($data));
    }
}
