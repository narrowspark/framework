<?php
declare(strict_types=1);
namespace Viserio\Parsers\Formats;

use RuntimeException;
use Viserio\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Contracts\Parsers\Exception\DumpException;
use Viserio\Contracts\Parsers\Exception\ParseException;
use Viserio\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Contracts\Parsers\Format as FormatContract;

class BSON implements FormatContract, DumperContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        if (function_exists('bson_decode')) {
            $bson = bson_decode(trim($payload));

            if (! $bson) {
                throw new ParseException([
                    'message' => 'Failed To Parse BSON',
                ]);
            }

            return $bson;
        }

        throw new RuntimeException('Failed To Parse BSON - Supporting Library Not Available');
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        $bson = bson_encode($data);

        if (! $bson) {
            throw new DumpException('BSON dumping failed.');
        }

        return $bson;
    }
}
