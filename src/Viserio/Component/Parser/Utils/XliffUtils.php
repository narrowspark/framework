<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Utils;

use DOMDocument;
use Viserio\Component\Contract\Parser\Exception\InvalidArgumentException;

final class XliffUtils
{
    /**
     * Private constructor; non-instantiable.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Validates and parses the given file into a DOMDocument.
     *
     * @param \DOMDocument $dom
     *
     * @throws \Viserio\Component\Contract\Parser\Exception\InvalidArgumentException
     *
     * @return array
     */
    public static function validateSchema(DOMDocument $dom): array
    {
        return XmlUtils::validateSchema($dom, self::getSchema(static::getVersionNumber($dom)));
    }

    /**
     * Gets xliff file version based on the root "version" attribute.
     * Defaults to 1.2 for backwards compatibility.
     *
     * @param \DOMDocument $dom
     *
     * @throws \Viserio\Component\Contract\Parser\Exception\InvalidArgumentException;
     *
     * @return string
     */
    public static function getVersionNumber(DOMDocument $dom): string
    {
        /** @var \DOMNode $xliff */
        foreach ($dom->getElementsByTagName('xliff') as $xliff) {
            if (($version = $xliff->attributes->getNamedItem('version')) !== null) {
                return $version->nodeValue;
            }

            if ($namespace = $xliff->attributes->getNamedItem('xmlns')) {
                if (\substr_compare('urn:oasis:names:tc:xliff:document:', $namespace, 0, 34) !== 0) {
                    throw new InvalidArgumentException(\sprintf('Not a valid XLIFF namespace [%s].', $namespace));
                }

                return \mb_substr($namespace, 34);
            }
        }

        return '1.2'; // Falls back to v1.2
    }

    /**
     * Get the right xliff schema from version.
     *
     * @param string $xliffVersion
     *
     * @throws \Viserio\Component\Contract\Parser\Exception\InvalidArgumentException;
     *
     * @return string
     */
    public static function getSchema(string $xliffVersion): string
    {
        if ($xliffVersion === '1.2') {
            $xmlUri       = 'http://www.w3.org/2001/xml.xsd';
            $schemaSource = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'schemas' . \DIRECTORY_SEPARATOR . 'xliff-core' . \DIRECTORY_SEPARATOR . 'xliff-core-1.2-strict.xsd';
        } elseif ($xliffVersion === '2.0') {
            $xmlUri       = 'informativeCopiesOf3rdPartySchemas/w3c/xml.xsd';
            $schemaSource = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'schemas' . \DIRECTORY_SEPARATOR . 'xliff-core' . \DIRECTORY_SEPARATOR . 'xliff-core-2.0.xsd';
        } else {
            throw new InvalidArgumentException(\sprintf('No support implemented for loading XLIFF version [%s].', $xliffVersion));
        }

        return self::fixLocation(\file_get_contents($schemaSource), $xmlUri);
    }

    /**
     * Internally changes the URI of a dependent xsd to be loaded locally.
     *
     * @param string $schemaSource Current content of schema file
     * @param string $xmlUri       External URI of XML to convert to local
     *
     * @return string
     */
    private static function fixLocation(string $schemaSource, string $xmlUri): string
    {
        $newPath = \str_replace('\\', '/', \dirname(__DIR__) . '/Resource/schemas/xliff-core/xml.xsd');
        $parts   = \explode('/', $newPath);

        if (\mb_stripos($newPath, 'phar://') === 0 && ($tmpFile = \tempnam(\sys_get_temp_dir(), 'narrowspark')) !== false) {
            \copy($newPath, $tmpFile);

            $parts = \explode('/', \str_replace('\\', '/', $tmpFile));
        }

        $drive   = '\\' === \DIRECTORY_SEPARATOR ? \array_shift($parts) . '/' : '';
        $newPath = 'file:///' . $drive . \implode('/', \array_map('rawurlencode', $parts));

        return \str_replace($xmlUri, $newPath, $schemaSource);
    }
}
