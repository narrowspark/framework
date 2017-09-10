<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Parser;

use Viserio\Component\Contract\Parsers\Exception\ParseException;
use Viserio\Component\Contract\Parsers\Parser as ParserContract;

class JsonParser implements ParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        $json = \json_decode(\trim($payload), true);

        if (\json_last_error() !== JSON_ERROR_NONE) {
            throw new ParseException([
                'message' => \json_last_error_msg(),
                'type'    => \json_last_error(),
                'file'    => $payload,
            ]);
        }

        return $json;
    }
}
