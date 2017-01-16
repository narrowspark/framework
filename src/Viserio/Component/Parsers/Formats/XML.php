<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Formats;

use DOMException;
use RuntimeException;
use Spatie\ArrayToXml\ArrayToXml;
use Throwable;
use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Component\Contracts\Parsers\Exception\DumpException;
use Viserio\Component\Contracts\Parsers\Exception\ParseException;
use Viserio\Component\Contracts\Parsers\Format as FormatContract;

class XML implements FormatContract, DumperContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        try {
            $data = simplexml_load_string($payload, 'SimpleXMLElement', LIBXML_NOCDATA);
            $data = json_decode(json_encode((array) $data), true); // Work around to accept xml input
            $data = str_replace(':{}', ':null', $data);
            $data = str_replace(':[]', ':null', $data);

            return $data;
        } catch (Throwable $exception) {
            throw new ParseException([
                'message' => 'Failed To Parse XML',
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        // @codeCoverageIgnoreStart
        if (! class_exists('Spatie\\ArrayToXml\\ArrayToXml')) {
            throw new RuntimeException('Unable to dump XML, the ArrayToXml dumper is not installed.');
        }
        // @codeCoverageIgnoreEnd

        try {
            return ArrayToXml::convert($data);
        } catch (DOMException $exception) {
            throw new DumpException($exception->getMessage());
        }
    }
}
