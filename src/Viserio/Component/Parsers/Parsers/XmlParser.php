<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Parsers;

use InvalidArgumentException;
use Viserio\Component\Contracts\Parsers\Exception\ParseException;
use Viserio\Component\Contracts\Parsers\Parser as ParserContract;
use Viserio\Component\Parsers\Utils\XmlUtils;

class XmlParser implements ParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        try {
            $dom  = XmlUtils::loadString($payload);
            // Work around to accept xml input
            $data = json_decode(json_encode((array) simplexml_import_dom($dom)), true);
            $data = str_replace(':{}', ':null', $data);
            $data = str_replace(':[]', ':null', $data);
        } catch (InvalidArgumentException $exception) {
            throw new ParseException([
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
            ]);
        }

        return $data;
    }
}
