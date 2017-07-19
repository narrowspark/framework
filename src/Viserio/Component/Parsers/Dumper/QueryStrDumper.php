<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Dumper;

use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;

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
