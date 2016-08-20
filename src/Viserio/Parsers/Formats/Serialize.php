<?php
declare(strict_types=1);
namespace Viserio\Parsers\Formats;

use Exception;
use Viserio\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Contracts\Parsers\Exception\DumpException;
use Viserio\Contracts\Parsers\Exception\ParseException;
use Viserio\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Contracts\Parsers\Format as FormatContract;

class Serialize implements FormatContract, DumperContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        try {
            return unserialize(trim($payload));
        } catch (Exception $exception) {
            throw new ParseException([
                'message' => 'Failed to parse serialized Data',
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        try {
            return serialize($data);
        } catch (Exception $exception) {
            throw new DumpException($exception->getMessage());
        }
    }
}
