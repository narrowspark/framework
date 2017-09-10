<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Fixtures;

use Viserio\Component\Contract\Parser\Dumper as DumperContract;

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
