<?php
namespace Viserio\Parsers\Formats;

use RuntimeException;
use Viserio\Contracts\Parsers\Exception\DumpException;
use Viserio\Contracts\Parsers\Exception\ParseException;
use Viserio\Contracts\Parsers\Format as FormatContract;

class BSON implements FormatContract
{
    /**
     * {@inheritdoc}
     */
    public function parse($payload)
    {
        if (function_exists('bson_decode')) {
            $bson = bson_decode(trim($payload));

            if (!$bson) {
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
    public function dump(array $data)
    {
    }
}
