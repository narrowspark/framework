<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Dumpers;

use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;

class SerializeDumper implements DumperContract
{
    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        return serialize($data);
    }
}
