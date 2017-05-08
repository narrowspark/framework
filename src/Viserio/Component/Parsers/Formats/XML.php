<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Formats;

use DOMException;
use RuntimeException;
use InvalidArgumentException;
use Spatie\ArrayToXml\ArrayToXml;
use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Component\Contracts\Parsers\Exception\DumpException;
use Viserio\Component\Contracts\Parsers\Exception\ParseException;
use Viserio\Component\Contracts\Parsers\Format as FormatContract;
use Viserio\Component\Parsers\Utils\XmlUtils;

class XML implements FormatContract, DumperContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        try {
            $dom  = XmlUtils::loadFile($payload);
            $data = XmlUtils::convertDomElementToArray($dom);
        } catch(InvalidArgumentException $exception) {
            throw new ParseException([
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
            ]);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        // @codeCoverageIgnoreStart
        if (! class_exists(ArrayToXml::class)) {
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
