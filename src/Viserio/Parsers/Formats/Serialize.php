<?php
namespace Viserio\Parsers\Formats;

use Exception;
use Viserio\Contracts\Parsers\Exception\DumpException;
use Viserio\Contracts\Parsers\Exception\ParseException;
use Viserio\Contracts\Parsers\Format as FormatContract;

class Serialize implements FormatContract
{
    /**
     * {@inheritdoc}
     */
    public function parse($payload)
    {
        try {
            return unserialize(trim($payload));
        } catch (Exception $exception) {
            throw new ParseException([
                'message' => 'Failed to parse serialized Data'
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data)
    {
        return serialize($data);
    }
}
