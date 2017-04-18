<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Formats;

use DOMException;
use RuntimeException;
use Spatie\ArrayToXml\ArrayToXml;
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
        libxml_use_internal_errors(true);

        $data = simplexml_load_string($payload, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($data === false) {
            $errors      = libxml_get_errors();
            $latestError = array_pop($errors);

            throw new ParseException([
                'message' => $latestError->message,
                'type'    => $latestError->level,
                'code'    => $latestError->code,
                'file'    => $latestError->file,
                'line'    => $latestError->line,
            ]);
        }

        $data = json_decode(json_encode((array) $data), true); // Work around to accept xml input
        $data = str_replace(':{}', ':null', $data);
        $data = str_replace(':[]', ':null', $data);

        return $data;
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
