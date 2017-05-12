<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Formats;

use DOMDocument;
use InvalidArgumentException;
use SimpleXMLElement;
use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Component\Contracts\Parsers\Exception\DumpException;
use Viserio\Component\Contracts\Parsers\Exception\ParseException;
use Viserio\Component\Contracts\Parsers\Format as FormatContract;
use Viserio\Component\Parsers\Utils\XmlUtils;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * This code has been ported from Symfony. The original
 * code is (c) Fabien Potencier <fabien@symfony.com>.
 *
 * Good article about xliff @link http://www.wikiwand.com/en/XLIFF
 */
class Xliff implements FormatContract, DumperContract
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        try {
            $dom = XmlUtils::loadString($payload);

            $xliffVersion = self::getVersionNumber($dom);
            self::validateSchema($xliffVersion, $dom, self::getSchema($xliffVersion));

            if ($xliffVersion === '2.0') {
                return $this->extractXliffVersion2($dom);
            }

            return $this->extractXliffVersion1($dom);
        } catch (InvalidArgumentException $exception) {
            throw new ParseException([
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        $version = $data['version'];

        unset($data['version']);

        if ($version === '1.2') {
            return self::dumpXliffVersion1($data);
        }

        if ($version === '2.0') {
            return self::dumpXliffVersion2($data);
        }

        throw new DumpException([
            'message' => sprintf('No support implemented for dumping XLIFF version "%s".', $version),
        ]);
    }

    /**
     * @param array $data
     *
     * @return string
     */
    private static function dumpXliffVersion1(array $data): string
    {
        $sourceLanguage = $data['source-language'];
        $targetLanguage = $data['target-language'];
        $encoding       = 'UTF-8';

        if (isset($data['encoding'])) {
            $encoding = $data['encoding'];

            unset($data['encoding']);
        }

        unset($data['source-language'], $data['target-language']);

        $dom               = new DOMDocument('1.0', $encoding);
        $dom->formatOutput = true;

        $xliff = $dom->appendChild($dom->createElement('xliff'));
        $xliff->setAttribute('version', '1.2');
        $xliff->setAttribute('xmlns', 'urn:oasis:names:tc:xliff:document:1.2');

        $xliffFile = $xliff->appendChild($dom->createElement('file'));
        $xliffFile->setAttribute('source-language', str_replace('_', '-', $sourceLanguage));

        if ($targetLanguage !== '') {
            $xliffFile->setAttribute('target-language', str_replace('_', '-', $targetLanguage));
        }

        $xliffFile->setAttribute('datatype', 'plaintext');
        $xliffFile->setAttribute('original', 'file.ext');

        $xliffBody = $xliffFile->appendChild($dom->createElement('body'));

        foreach ($data as $resname => $translation) {
            $unit = $dom->createElement('trans-unit');
            $unit->setAttribute('id', $translation['id'] ?? md5($resname));
            $unit->setAttribute('resname', $resname);

            $source = $unit->appendChild($dom->createElement('source'));
            $source->appendChild($dom->createTextNode($translation['source']));

            $targetElement = $dom->createElement('target');

            if (isset($translation['target-attributes'])) {
                foreach ($translation['target-attributes'] as $key => $value) {
                    $targetElement->setAttribute($name, $value);
                }
            }

            $target = $unit->appendChild($targetElement);

            // Does the target contain characters requiring a CDATA section?
            if (preg_match('/[&<>]/', $translation['target']) === 1) {
                $target->appendChild($dom->createCDATASection($translation['target']));
            } else {
                $target->appendChild($dom->createTextNode($translation['target']));
            }

            if (isset($translation['notes'])) {
                foreach ($translation['notes'] as $note) {
                    $noteElement = $dom->createElement('note');
                    $noteElement->appendChild($dom->createTextNode($note['content']));

                    if (isset($note['priority'])) {
                        $noteElement->setAttribute('priority', $note['priority']);
                    } elseif (isset($note['from'])) {
                        $noteElement->setAttribute('from', $note['from']);
                    }
                }

                $unit->appendChild($noteElement);
            }

            $xliffBody->appendChild($unit);
        }

        return $dom->saveXML();
    }

    /**
     * @param array $data
     *
     * @return string
     */
    private static function dumpXliffVersion2(array $data): string
    {
        $sourceLanguage = $data['srcLang'];
        $targetLanguage = $data['trgLang'];
        $encoding       = 'UTF-8';

        if (isset($data['encoding'])) {
            $encoding = $data['encoding'];

            unset($data['encoding']);
        }

        unset($data['srcLang'], $data['trgLang']);

        $dom               = new DOMDocument('1.0', $encoding);
        $dom->formatOutput = true;

        $xliff = $dom->appendChild($dom->createElement('xliff'));
        $xliff->setAttribute('xmlns', 'urn:oasis:names:tc:xliff:document:2.0');
        $xliff->setAttribute('version', '2.0');
        $xliff->setAttribute('srcLang', str_replace('_', '-', $sourceLanguage));
        $xliff->setAttribute('trgLang', str_replace('_', '-', $targetLanguage));

        $xliffFile = $xliff->appendChild($dom->createElement('file'));
        $xliffFile->setAttribute(
            'id',
            'translation_' . strtolower(str_replace('-', '_', $sourceLanguage . '_to_' . $targetLanguage))
        );

        foreach ($data as $id => $translation) {
            $unit = $dom->createElement('unit');
            $unit->setAttribute('id', $id);

            $segmentElement = $unit->appendChild($dom->createElement('segment'));
            $source        = $segmentElement->appendChild($dom->createElement('source'));
            $source->appendChild($dom->createTextNode($translation['source']));

            $targetElement = $dom->createElement('target');

            if (isset($translation['target-attributes'])) {
                foreach ($translation['target-attributes'] as $key => $value) {
                    $targetElement->setAttribute($name, $value);
                }
            }

            $target = $segmentElement->appendChild($targetElement);

            // Does the target contain characters requiring a CDATA section?
            if (preg_match('/[&<>]/', $translation['target']) === 1) {
                $target->appendChild($dom->createCDATASection($translation['target']));
            } else {
                $target->appendChild($dom->createTextNode($translation['target']));
            }

            $xliffFile->appendChild($unit);
        }

        return $dom->saveXML();
    }

    /**
     * Extract messages and metadata from DOMDocument into a MessageCatalogue.
     *
     * @param \DOMDocument $dom
     *
     * @return array
     */
    private function extractXliffVersion1(DOMDocument $dom): array
    {
        $xml       = simplexml_import_dom($dom);
        $encoding  = mb_strtoupper($dom->encoding);
        $datas     = [
            'version'         => '1.2',
            'source-language' => '',
            'target-language' => '',
        ];

        foreach ($xml->file->attributes() as $key => $value) {
            if ($key === 'source-language' || $key === 'target-language') {
                $datas[$key] = (string) $value;
            }
        }

        $xml->registerXPathNamespace('xliff', 'urn:oasis:names:tc:xliff:document:1.2');

        foreach ($xml->xpath('//xliff:trans-unit') as $trans) {
            $attributes = $trans->attributes();
            $id         = (string) ($attributes['resname'] ?? $trans->source ?? '');

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

            if (isset($trans->target) && ($attributes = $trans->target->attributes())) {
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
     * @param \SimpleXMLElement $noteElement
     * @param string|null       $encoding
     *
     * @return array
     */
    private static function parseNotes(SimpleXMLElement $noteElement, ?string $encoding = null): array
    {
        $notes = [];

        /** @var \SimpleXMLElement $xmlNote */
        foreach ($noteElement as $xmlNote) {
            $noteAttributes = $xmlNote->attributes();
            $note           = ['content' => self::utf8ToCharset((string) $xmlNote, $encoding)];

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
     * @param \DOMDocument $dom
     *
     * @return array
     */
    private function extractXliffVersion2(DOMDocument $dom): array
    {
        $xml      = simplexml_import_dom($dom);
        $encoding = mb_strtoupper($dom->encoding);
        $datas    = [
            'version'  => '2.0',
            'srcLang'  => '',
            'trgLang'  => '',
        ];

        foreach ($xml->attributes() as $key => $value) {
            if ($key === 'srcLang' || $key === 'trgLang') {
                $datas[$key] = (string) $value;
            }
        }

        $xml->registerXPathNamespace('xliff', 'urn:oasis:names:tc:xliff:document:2.0');

        foreach ($xml->xpath('//xliff:unit') as $unit) {
            $unitAttr = (array)$unit->attributes();
            $source = (string) $unit->segment->source;
            $target = null;
            $id = $unitAttr['@attributes']['id'];

            if (isset($unit->segment->target)) {
                $target = self::utf8ToCharset((string) $unit->segment->target, $encoding);
            }

            $datas[$id] = [
                'source'   => $source,
                // If the xlf file has another encoding specified, try to convert it because
                // simple_xml will always return utf-8 encoded values
                'target'   => $target,
            ];

            if ($target !== null && $unit->segment->target->attributes()) {
                $datas[$id]['target-attributes'] = [];

                foreach ($unit->segment->target->attributes() as $key => $value) {
                    $datas[$id]['target-attributes'][$key] = (string) $value;
                }
            }
        }

        return $datas;
    }

    /**
     * Gets xliff file version based on the root "version" attribute.
     * Defaults to 1.2 for backwards compatibility.
     *
     * @param \DOMDocument $dom
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    private static function getVersionNumber(DOMDocument $dom): string
    {
        /** @var \DOMNode $xliff */
        foreach ($dom->getElementsByTagName('xliff') as $xliff) {
            if ($version = $xliff->attributes->getNamedItem('version')) {
                return $version->nodeValue;
            }

            if ($namespace = $xliff->namespaceURI) {
                if (substr_compare('urn:oasis:names:tc:xliff:document:', $namespace, 0, 34) !== 0) {
                    throw new InvalidArgumentException(sprintf('Not a valid XLIFF namespace "%s"', $namespace));
                }

                return mb_substr($namespace, 34);
            }
        }

        return '1.2'; // Falls back to v1.2
    }

    /**
     * Validates and parses the given file into a DOMDocument.
     *
     * @param string       $file
     * @param \DOMDocument $dom
     * @param string       $schema source of the schema
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    private static function validateSchema($file, DOMDocument $dom, string $schema): void
    {
        $internalErrors  = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(false);

        if (! @$dom->schemaValidateSource($schema)) {
            libxml_disable_entity_loader($disableEntities);

            throw new InvalidArgumentException(
                sprintf(
                    'Invalid resource provided: "%s"; Errors: %s',
                    $file,
                    implode("\n", XmlUtils::getXmlErrors($internalErrors))
                )
            );
        }

        libxml_disable_entity_loader($disableEntities);

        $dom->normalizeDocument();

        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);
    }

    /**
     * Get the right xliff schema from version.
     *
     * @param string $xliffVersion
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    private static function getSchema(string $xliffVersion): string
    {
        if ($xliffVersion === '1.2') {
            $xmlUri       = 'http://www.w3.org/2001/xml.xsd';
            $schemaSource = self::normalizeDirectorySeparator(__DIR__ . '/../Schemas/xliff-core/xliff-core-1.2-strict.xsd');
        } elseif ($xliffVersion === '2.0') {
            $xmlUri       = 'informativeCopiesOf3rdPartySchemas/w3c/xml.xsd';
            $schemaSource = self::normalizeDirectorySeparator(__DIR__ . '/../Schemas/xliff-core/xliff-core-2.0.xsd');
        } else {
            throw new InvalidArgumentException(sprintf('No support implemented for loading XLIFF version "%s".', $xliffVersion));
        }

        return self::fixXmlLocation(file_get_contents($schemaSource), $xmlUri);
    }

    /**
     * Convert a UTF8 string to the specified encoding.
     *
     * @param string      $content  String to decode
     * @param string|null $encoding Target encoding
     *
     * @return string
     */
    private static function utf8ToCharset(string $content, string $encoding = null): string
    {
        if ($encoding !== 'UTF-8' && ! empty($encoding)) {
            return mb_convert_encoding($content, $encoding, 'UTF-8');
        }

        return $content;
    }

    /**
     * Internally changes the URI of a dependent xsd to be loaded locally.
     *
     * @param string $schemaSource Current content of schema file
     * @param string $xmlUri       External URI of XML to convert to local
     *
     * @return string
     */
    private static function fixXmlLocation(string $schemaSource, string $xmlUri): string
    {
        $newPath = str_replace('\\', '/', realpath(__DIR__ . '/../Schemas/xliff-core/xml.xsd'));
        $parts   = explode('/', $newPath);

        if (mb_stripos($newPath, 'phar://') === 0) {
            if ($tmpfile = tempnam(sys_get_temp_dir(), 'narrowspark')) {
                copy($newPath, $tmpfile);
                $parts = explode('/', str_replace('\\', '/', $tmpfile));
            }
        }

        $drive   = '\\' === DIRECTORY_SEPARATOR ? array_shift($parts) . '/' : '';
        $newPath = 'file:///' . $drive . implode('/', array_map('rawurlencode', $parts));

        return str_replace($xmlUri, $newPath, $schemaSource);
    }
}
