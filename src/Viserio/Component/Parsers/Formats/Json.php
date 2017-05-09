<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Formats;

use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Component\Contracts\Parsers\Exception\DumpException;
use Viserio\Component\Contracts\Parsers\Exception\ParseException;
use Viserio\Component\Contracts\Parsers\Format as FormatContract;

class Json implements FormatContract, DumperContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        $json = json_decode(trim($payload), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ParseException([
                'message' => json_last_error_msg(),
                'type'    => json_last_error(),
                'file'    => $payload,
            ]);
        }

        return $json;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        // Clear json_last_error()
        json_encode(null);

        $json = json_encode($data, JSON_PRETTY_PRINT);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // @codeCoverageIgnoreStart
            throw new DumpException(sprintf('JSON dumping failed: %s', json_last_error_msg()));
            // @codeCoverageIgnoreEnd
        }

        $json = preg_replace('/\[\s+\]/', '[]', $json);
        $json = preg_replace('/\{\s+\}/', '{}', $json);

        return $json;
    }
}
