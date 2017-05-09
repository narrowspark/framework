<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Formats;

use DOMDocument;
use InvalidArgumentException;
use Viserio\Component\Contracts\Parsers\Exception\ParseException;
use Viserio\Component\Contracts\Parsers\Format as FormatContract;
use Viserio\Component\Parsers\Utils\XmlUtils;

class Xliff implements FormatContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        if (! file_exists($payload)) {
            throw new ParseException(['message' => 'File not found.']);
        }

        try {
            $dom = XmlUtils::loadFile($resource);
        } catch (InvalidArgumentException $exception) {
            throw new ParseException([
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
            ]);
        }

        $xliffVersion = $this->getVersionNumber($dom);
        $this->validateSchema($xliffVersion, $dom, $this->getSchema($xliffVersion));

        if ($xliffVersion === '1.2') {
            return $this->extractXliff1($dom);
        } elseif ($xliffVersion === '2.0') {
            return $this->extractXliff2($dom);
        }

        throw new ParseException(['message' => '']);
    }

    /**
     * Extract messages and metadata from DOMDocument into a MessageCatalogue.
     *
     * @param \DOMDocument $dom
     */
    private function extractXliff1(DOMDocument $dom)
    {
        $xml      = simplexml_import_dom($dom);
        $encoding = mb_strtoupper($dom->encoding);

        $xml->registerXPathNamespace('xliff', 'urn:oasis:names:tc:xliff:document:1.2');
        $xml->registerXPathNamespace('narrowspark', 'urn:narrowspark:translation');
    }

    /**
     * Extract messages and metadata from DOMDocument into a MessageCatalogue.
     *
     * @param \DOMDocument $dom
     */
    private function extractXliff2(DOMDocument $dom)
    {
        $xml      = simplexml_import_dom($dom);
        $encoding = mb_strtoupper($dom->encoding);

        $xml->registerXPathNamespace('xliff', 'urn:oasis:names:tc:xliff:document:2.0');
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
    private function getVersionNumber(DOMDocument $dom): string
    {
        /** @var \DOMNode $xliff */
        foreach ($dom->getElementsByTagName('xliff') as $xliff) {
            $version = $xliff->attributes->getNamedItem('version');

            if ($version) {
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
        // Falls back to v1.2
        return '1.2';
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
    private function validateSchema(string $file, DOMDocument $dom, string $schema): void
    {
        $internalErrors  = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(false);

        if (! @$dom->schemaValidateSource($schema)) {
            libxml_disable_entity_loader($disableEntities);

            throw new InvalidArgumentException(
                sprintf(
                    'Invalid resource provided: "%s"; Errors: %s',
                    $file,
                    implode("\n", $this->getXmlErrors($internalErrors))
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
     * @return string
     */
    private function getSchema(string $xliffVersion): string
    {
        if ($xliffVersion === '1.2') {
            //http://www.w3.org/2001/xml.xsd
            $schemaSource = file_get_contents(__DIR__ . '/../Schemas/dic/xliff-core/xliff-core-1.2-strict.xsd');
        } elseif ($xliffVersion === '2.0') {
            // informativeCopiesOf3rdPartySchemas/w3c/xml.xsd
            $schemaSource = file_get_contents(__DIR__ . '/../Schemas/dic/xliff-core/xliff-core-2.0.xsd');
        } else {
            throw new InvalidArgumentException(sprintf('No support implemented for loading XLIFF version "%s".', $xliffVersion));
        }

        return $schemaSource;
    }

    /**
     * Returns the XML errors of the internal XML parser.
     *
     * @param bool $internalErrors
     *
     * @return array An array of errors
     */
    private function getXmlErrors(bool $internalErrors): array
    {
        $errors = [];

        foreach (libxml_get_errors() as $error) {
            $errors[] = sprintf('[%s %s] %s (in %s - line %d, column %d)',
                LIBXML_ERR_WARNING == $error->level ? 'WARNING' : 'ERROR',
                $error->code,
                trim($error->message),
                $error->file ?: 'n/a',
                $error->line,
                $error->column
            );
        }

        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);

        return $errors;
    }

    /**
     * Convert a UTF8 string to the specified encoding.
     *
     * @param string      $content  String to decode
     * @param string|null $encoding Target encoding
     *
     * @return string
     */
    private function utf8ToCharset(string $content, string $encoding = null): string
    {
        if ($encoding !== 'UTF-8' && ! empty($encoding)) {
            return mb_convert_encoding($content, $encoding, 'UTF-8');
        }

        return $content;
    }
}
