<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Formats;

use DOMDocument;
use InvalidArgumentException;
use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Component\Contracts\Parsers\Exception\ParseException;
use Viserio\Component\Contracts\Parsers\Format as FormatContract;
use Viserio\Component\Parsers\Utils\XmlUtils;

/**
 * For more infos.
 *
 * @link http://doc.qt.io/qt-5/linguist-ts-file-format.html
 * @link http://svn.ez.no/svn/ezcomponents/trunk/Translation/docs/linguist-format.txt
 */
class Qt implements FormatContract, DumperContract
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

    /**
     * {@inheritdoc}
     *
     * array['name']                    string      the id to group translation and to create the name element.
     *     array[]
     *          ['source']              string
     *          array['translation']
     *              array['content']    string      content of the translation element
     *              array['attributes'] false|array attributes for the translation element; simple key value array
     */
    public function dump(array $data): string
    {
        $dom               = new DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;

        $ts = $dom->appendChild($dom->createElement('TS'));

        foreach ($data as $name => $groups) {
            $context = $ts->appendChild($dom->createElement('context'));
            $context->appendChild($dom->createElement('name', $name));

            foreach ($groups as $key => $value) {
                $message = $context->appendChild($dom->createElement('message'));
                $message->appendChild($dom->createElement('source', $value['source']));

                $translation = $dom->createElement('translation', $value['translation']['content']);
                $attributes  = $value['translation']['attributes'];

                if (is_array($attributes)) {
                    foreach ($attributes as $key => $value) {
                        $translation->setAttribute($key, $value);
                    }
                }

                $message->appendChild($translation);
            }
        }

        return $dom->saveXML();
    }
}
