<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Dumper;

use Viserio\Component\Contract\Parser\Dumper as DumperContract;
use Viserio\Component\Contract\Parser\Exception\DumpException;

class JsonDumper implements DumperContract
{
    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        // Clear json_last_error()
        \json_encode(null);

        $json = \json_encode($data, \JSON_PRETTY_PRINT);

        if (\json_last_error() !== \JSON_ERROR_NONE) {
            // @codeCoverageIgnoreStart
            throw new DumpException(\sprintf('JSON dumping failed: %s', \json_last_error_msg()));
            // @codeCoverageIgnoreEnd
        }

        $json = \preg_replace('/\[\s+\]/', '[]', $json);

        return \preg_replace('/\{\s+\}/', '{}', $json);
    }
}
