<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Formats;

use DOMDocument;
use InvalidArgumentException;
use SimpleXMLElement;
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
class Xliff implements FormatContract
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        try {
            $dom = XmlUtils::loadString($payload);
        } catch (InvalidArgumentException $exception) {
            throw new ParseException([
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
            ]);
        }

        $xliffVersion = self::getVersionNumber($dom);
        self::validateSchema($xliffVersion, $dom, self::getSchema($xliffVersion));

        if ($xliffVersion === '2.0') {
            return $this->extractXliffVersion2($dom);
        }

        return $this->extractXliffVersion1($dom);
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
        $datas     = [];

        $xml->registerXPathNamespace('xliff', 'urn:oasis:names:tc:xliff:document:1.2');

        foreach ($xml->xpath('//xliff:trans-unit') as $trans) {
            $attributes = $trans->attributes();

            if (! isset($attributes['resname']) || empty($trans->source)) {
                continue;
            }

            $id = (string) ($attributes['resname'] ?? $trans->source);

            $datas[$id] = [
                'source' => (string) $trans->source,
                // If the xlf file has another encoding specified, try to convert it because
                // simple_xml will always return utf-8 encoded values
                'target' => isset($trans->target) ? self::utf8ToCharset((string) $trans->target) : null,
            ];

            if (isset($attributes['id'])) {
                $datas[$id]['id'] = (string) $attributes['id'];
            }

            if (isset($trans->target['state'])) {
                $datas[$id]['state'] = $trans->target['state'];
            }

            // If the translation has a note
            if (isset($trans->note)) {
                $datas[$id]['notes'] = self::parseNotes($trans->note);
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
     * @param \SimpleXMLElement|null $noteElement
     * @param string|null            $encoding
     *
     * @return array
     */
    private static function parseNotes(?SimpleXMLElement $noteElement = null, ?string $encoding = null): array
    {
        if ($noteElement === null) {
            return [];
        }

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
        $datas    = [];

        $xml->registerXPathNamespace('xliff', 'urn:oasis:names:tc:xliff:document:2.0');

        foreach ($xml->xpath('//xliff:unit/xliff:segment') as $segment) {
            $datas[(string) $segment->source] = [
                'source'   => (string) $segment->source,
                // If the xlf file has another encoding specified, try to convert it because
                // simple_xml will always return utf-8 encoded values
                'target'   => isset($segment->target) ? self::utf8ToCharset((string) $segment->target) : null,
            ];

            if (isset($segment->target) && $segment->target->attributes()) {
                $datas[$segment->source]['target-attributes'] = [];

                foreach ($segment->target->attributes() as $key => $value) {
                    $datas[$segment->source]['target-attributes'][$key] = (string) $value;
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

            $namespace = $xliff->attributes->getNamedItem('xmlns');

            if ($namespace) {
                if (substr_compare('urn:oasis:names:tc:xliff:document:', $namespace->nodeValue, 0, 34) !== 0) {
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
            $tmpfile = tempnam(sys_get_temp_dir(), 'sf2');

            if ($tmpfile) {
                copy($newPath, $tmpfile);
                $parts = explode('/', str_replace('\\', '/', $tmpfile));
            }
        }

        $drive   = '\\' === DIRECTORY_SEPARATOR ? array_shift($parts) . '/' : '';
        $newPath = 'file:///' . $drive . implode('/', array_map('rawurlencode', $parts));

        return str_replace($xmlUri, $newPath, $schemaSource);
    }
}
