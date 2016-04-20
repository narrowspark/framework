<?php
namespace Viserio\Parsers\Formats;

use Viserio\Contracts\Parsers\Exception\DumpException;
use Viserio\Contracts\Parsers\Exception\ParseException;
use Viserio\Contracts\Parsers\Format as FormatContract;
use RuntimeException;

class MSGPack implements FormatContract
{
    /**
     * {@inheritdoc}
     */
    public function parse($payload)
    {
        if (function_exists('msgpack_unpack')) {
            $msg = msgpack_unpack(trim($payload));

            if (!$msg) {
                throw new ParseException([
                    'message' => 'Failed To Parse MSGPack'
                ]);
            }

            return $msg;
        }

        throw new RuntimeException('Failed To Parse MSGPack - Supporting Library Not Available');
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data)
    {
    }
}
