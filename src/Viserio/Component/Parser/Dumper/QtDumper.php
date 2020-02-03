<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Parser\Dumper;

use DOMDocument;
use Viserio\Contract\Parser\Dumper as DumperContract;

/**
 * For more infos.
 *
 * @see http://doc.qt.io/qt-5/linguist-ts-file-format.html
 * @see http://svn.ez.no/svn/ezcomponents/trunk/Translation/docs/linguist-format.txt
 */
class QtDumper implements DumperContract
{
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
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;

        $ts = $dom->appendChild($dom->createElement('TS'));

        foreach ($data as $name => $groups) {
            $context = $ts->appendChild($dom->createElement('context'));
            $context->appendChild($dom->createElement('name', (string) $name));

            foreach ($groups as $key => $value) {
                $message = $context->appendChild($dom->createElement('message'));
                $message->appendChild($dom->createElement('source', $value['source']));

                $translation = $dom->createElement('translation', $value['translation']['content']);
                $attributes = $value['translation']['attributes'];

                if (\is_array($attributes)) {
                    foreach ($attributes as $k => $v) {
                        $translation->setAttribute($k, $v);
                    }
                }

                $message->appendChild($translation);
            }
        }

        return $dom->saveXML();
    }
}
