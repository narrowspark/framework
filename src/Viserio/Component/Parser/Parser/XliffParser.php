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

use DOMDocument;
use PHPUnit\Util\Xml;
use SimpleXMLElement;
use Viserio\Component\Parser\Utils\XliffUtils;
use Viserio\Component\Parser\Utils\XmlUtils;
use Viserio\Contract\Parser\Exception\InvalidArgumentException;
use Viserio\Contract\Parser\Exception\ParseException;
use Viserio\Contract\Parser\Parser as ParserContract;

/**
 * Some of this code has been ported from Symfony. The original
 * code is (c) Fabien Potencier <fabien@symfony.com>.
 *
 * Good article about xliff @link http://www.wikiwand.com/en/XLIFF
 */
class XliffParser implements ParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        try {
            $dom = XmlUtils::loadString($payload);

            $xliffVersion = XliffUtils::getVersionNumber($dom);
            $errors = XliffUtils::validateSchema($dom);

            if (\count($errors) !== 0) {
                throw new InvalidArgumentException(\sprintf('Invalid resource provided: [%s]; Errors: %s.', $xliffVersion, XmlUtils::getErrorsAsString($errors)));
            }

            if ($xliffVersion === '2.0') {
                return $this->extractXliffVersion2($dom);
            }

            return $this->extractXliffVersion1($dom);
        } catch (InvalidArgumentException $exception) {
            throw ParseException::createFromException($exception->getMessage(), $exception);
        }
    }

    /**
     * Extract messages and metadata from DOMDocument into a MessageCatalogue.
     *
     * @throws \Viserio\Contract\Parser\Exception\ParseException
     *
     * @return array<int|string, mixed>
     */
    private function extractXliffVersion1(DOMDocument $dom): array
    {
        $xml = XmlUtils::importDom($dom);

        $encoding = \strtoupper($dom->encoding);
        $datas = [
            'version' => '1.2',
            'source-language' => '',
            'target-language' => '',
        ];

        if (($attributes = $xml->file->attributes()) !== null) {
            foreach ($attributes as $key => $value) {
                if ($key === 'source-language' || $key === 'target-language') {
                    $datas[$key] = (string) $value;
                }
            }
        }

        $xml->registerXPathNamespace('xliff', 'urn:oasis:names:tc:xliff:document:1.2');

        /** @var SimpleXMLElement $trans */
        foreach ((array) $xml->xpath('//xliff:trans-unit') as $trans) {
            $attributes = $trans->attributes();
            $id = (string) ($attributes['resname'] ?? $trans->source ?? '');

            if ($id === '') {
                continue;
            }

            $datas[$id] = [
                'source' => (string) $trans->source,
                // If the xlf file has another encoding specified, try to convert it because
                // simple_xml will always return utf-8 encoded values
                'target' => isset($trans->target) ? self::utf8ToCharset((string) $trans->target, $encoding) : null,
            ];

            if (isset($attributes['id'])) {
                $datas[$id]['id'] = (string) $attributes['id'];
            }

            // If the translation has a note
            if (isset($trans->note)) {
                $datas[$id]['notes'] = self::parseNotes($trans->note, $encoding);
            }

            if (isset($trans->target) && ($attributes = $trans->target->attributes()) !== null && \count($attributes) !== 0) {
                $datas[$id]['target-attributes'] = [];

                foreach ($attributes as $key => $value) {
                    $datas[$id]['target-attributes'][$key] = (string) $value;
                }
            }
        }

        return $datas;
    }

    /**
     * Parse xliff notes.
     *
     * @return array<int|string, mixed>
     */
    private static function parseNotes(SimpleXMLElement $noteElement, ?string $encoding = null): array
    {
        $notes = [];

        /** @var SimpleXMLElement $xmlNote */
        foreach ($noteElement as $xmlNote) {
            $noteAttributes = $xmlNote->attributes();
            $note = ['content' => self::utf8ToCharset((string) $xmlNote, $encoding)];

            if (isset($noteAttributes['priority'])) {
                $note['priority'] = (int) $noteAttributes['priority'];
            }

            if (isset($noteAttributes['from'])) {
                $note['from'] = (string) $noteAttributes['from'];
            }

            $notes[] = $note;
        }

        return $notes;
    }

    /**
     * Extract messages and metadata from DOMDocument into a MessageCatalogue.
     *
     * @throws \Viserio\Contract\Parser\Exception\ParseException
     *
     * @return array<int|string, mixed>
     */
    private function extractXliffVersion2(DOMDocument $dom): array
    {
        $xml = XmlUtils::importDom($dom);

        $encoding = \strtoupper($dom->encoding);
        $datas = [
            'version' => '2.0',
            'srcLang' => '',
            'trgLang' => '',
        ];

        if (($attributes = $xml->attributes()) !== null) {
            foreach ($attributes as $key => $value) {
                if ($key === 'srcLang' || $key === 'trgLang') {
                    $datas[$key] = (string) $value;
                }
            }
        }

        $xml->registerXPathNamespace('xliff', 'urn:oasis:names:tc:xliff:document:2.0');

        /** @var SimpleXMLElement $unit */
        foreach ((array) $xml->xpath('//xliff:unit') as $unit) {
            $unitAttr = (array) $unit->attributes();
            $unitAttr = \reset($unitAttr);

            foreach ($unit->segment as $segment) {
                $source = (string) $segment->source;
                $target = null;

                $id = $unitAttr['id'];

                if (isset($segment->target)) {
                    $target = self::utf8ToCharset((string) $segment->target, $encoding);
                }

                $datas[$id] = [
                    'source' => $source,
                    // If the xlf file has another encoding specified, try to convert it because
                    // simple_xml will always return utf-8 encoded values
                    'target' => $target,
                ];

                if ($target !== null && isset($segment->target) && \count($attributes = $segment->target->attributes()) !== 0) {
                    $datas[$id]['target-attributes'] = [];

                    foreach ($attributes as $key => $value) {
                        $datas[$id]['target-attributes'][$key] = (string) $value;
                    }
                }

                if (isset($unit->notes)) {
                    foreach ($unit->notes->note as $noteNode) {
                        $note = [];

                        foreach ($noteNode->attributes() as $key => $value) {
                            $note[$key] = (string) $value;
                        }

                        $note['content'] = (string) $noteNode;
                        $datas[$id]['notes'][] = $note;
                    }
                }
            }
        }

        return $datas;
    }

    /**
     * Convert a UTF8 string to the specified encoding.
     *
     * @param string      $content  String to decode
     * @param null|string $encoding Target encoding
     */
    private static function utf8ToCharset(string $content, ?string $encoding = null): string
    {
        if ($encoding !== 'UTF-8' && $encoding !== null) {
            return \mb_convert_encoding($content, $encoding, 'UTF-8');
        }

        return $content;
    }
}
