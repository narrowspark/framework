<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Parser\Parser;

use InvalidArgumentException;
use Viserio\Component\Parser\Utils\XmlUtils;
use Viserio\Contract\Parser\Exception\ParseException;
use Viserio\Contract\Parser\Parser as ParserContract;

/**
 * For more infos.
 *
 * @see http://doc.qt.io/qt-5/linguist-ts-file-Parser.html
 * @see http://svn.ez.no/svn/ezcomponents/trunk/Translation/docs/linguist-Parser.txt
 */
class QtParser implements ParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        try {
            $dom = XmlUtils::loadString($payload);
        } catch (InvalidArgumentException $exception) {
            throw ParseException::createFromException($exception->getMessage(), $exception);
        }

        $internalErrors = \libxml_use_internal_errors(true);

        \libxml_clear_errors();

        $xml = XmlUtils::importDom($dom);
        $datas = [];

        if (($context = $xml->xpath('//TS/context')) !== false) {
            foreach ($context as $node) {
                $name = (string) $node->name;
                $datas[$name] = [];

                foreach ($node->message as $message) {
                    $translation = $message->translation;
                    $translationAttributes = (array) $translation->attributes();
                    $attributes = \reset($translationAttributes);

                    $datas[$name][] = [
                        'source' => (string) $message->source,
                        'translation' => [
                            'content' => (string) $translation,
                            'attributes' => $attributes,
                        ],
                    ];
                }
            }
        }

        \libxml_use_internal_errors($internalErrors);

        return $datas;
    }
}
