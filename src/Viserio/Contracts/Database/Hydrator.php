<?php
declare(strict_types=1);
namespace Viserio\Contracts\Database;

interface Hydrator
{
    const HYDRATE_COLLECTION = 'collection';

    const HYDRATE_ENTITY = 'entity';

    const HYDRATE_RAW = 'raw';

    const HYDRATE_AUTO = 'auto';

    /**
     * Extract values from received object
     *
     * @return array
     */
    public function extract(): array;

    /**
     * Hydrate received object with the provided $data.
     *
     * @param array  $data
     * @param string $option
     *
     * @return mixed
     */
    public function hydrate($data = [], $option = self::HYDRATE_AUTO);
}
