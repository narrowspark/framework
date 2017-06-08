<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Parsers;

use InvalidArgumentException;
use Viserio\Component\Contracts\Parsers\Exceptions\ParseException;
use Viserio\Component\Contracts\Parsers\Parser as ParserContract;
use Viserio\Component\Parsers\Utils\XmlUtils;

/**
 * For more infos.
 *
 * @link http://doc.qt.io/qt-5/linguist-ts-file-Parser.html
 * @link http://svn.ez.no/svn/ezcomponents/trunk/Translation/docs/linguist-Parser.txt
 */
class QtParser implements ParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        try {
            $dom  = XmlUtils::loadString($payload);
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

        $xpath  = simplexml_import_dom($dom);
        $nodes  = $xpath->xpath('//TS/context');
        $datas  = [];

        foreach ($nodes as $node) {
            $name         = (string) $node->name;
            $datas[$name] = [];

            foreach ($node->message as $message) {
                $translation           = $message->translation;
                $translationAttributes = (array) $translation->attributes();
                $attributes            = reset($translationAttributes);

                $datas[$name][] = [
                    'source'      => (string) $message->source,
                    'translation' => [
                        'content'    => (string) $translation,
                        'attributes' => $attributes,
                    ],
                ];
            }
        }

        libxml_use_internal_errors($internalErrors);

        return $datas;
    }
}
