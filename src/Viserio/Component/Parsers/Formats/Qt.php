<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Formats;

use DOMXPath;
use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Component\Contracts\Parsers\Format as FormatContract;
use Viserio\Component\Parsers\Utils\XmlUtils;

class Qt implements FormatContract, DumperContract
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

        $internalErrors = libxml_use_internal_errors(true);

        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $nodes = $xpath->evaluate('//TS/context/name[text()=""]');
        $data = [];

        if ($nodes->length == 1) {
            $values = $nodes->item(0)->nextSibling->parentNode->parentNode->getElementsByTagName('message');

            foreach ($values as $value) {
                $translationValue = (string) $value->getElementsByTagName('translation')->item(0)->nodeValue;

                if (!empty($translationValue)) {
                    $data[] =[
                        'source' => (string) $value->getElementsByTagName('source')->item(0)->nodeValue,
                        'target' => $translationValue
                    ];
                }

                $value = $value->nextSibling;
            }
        }

        libxml_use_internal_errors($internalErrors);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
    }
}
