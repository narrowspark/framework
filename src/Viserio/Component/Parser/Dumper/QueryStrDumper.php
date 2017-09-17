<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Dumper;

use Viserio\Component\Contract\Parser\Dumper as DumperContract;

class QueryStrDumper implements DumperContract
{
    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        return \http_build_query($data);
    }
}
