<?php
namespace Viserio\Parsers\Formats;

use RuntimeException;
use Viserio\Contracts\Parsers\{
    Exception\DumpException,
    Exception\ParseException,
    Format as FormatContract
};

class MSGPack implements FormatContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        if (function_exists('msgpack_unpack')) {
            $msg = msgpack_unpack(trim($payload));

            if (! $msg) {
                throw new ParseException([
                    'message' => 'Failed To Parse MSGPack',
                ]);
            }

            return $msg;
        }

        throw new RuntimeException('Failed To Parse MSGPack - Supporting Library Not Available');
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        $msg = msgpack_pack($data);

        if (! $msg) {
            throw new DumpException('MSGPack dumping failed.');
        }

        return $msg;
    }
}
