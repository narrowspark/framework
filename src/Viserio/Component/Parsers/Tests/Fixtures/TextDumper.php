<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Fixtures;

use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;

class TextDumper implements DumperContract
{
    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        return $data[0];
    }
}
