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

namespace Viserio\Component\Parser\Utils;

use DOMDocument;
use DOMNode;
use Viserio\Contract\Parser\Exception\InvalidArgumentException;
use Viserio\Contract\Parser\Exception\RuntimeException;

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
     * @param DOMDocument $dom
     *
     * @throws \Viserio\Contract\Parser\Exception\InvalidArgumentException
     *
     * @return array<int, array<string, int|string>>
     */
    public static function validateSchema(DOMDocument $dom): array
    {
        return XmlUtils::validateSchema($dom, self::getSchema(self::getVersionNumber($dom)));
    }

    /**
     * Gets xliff file version based on the root "version" attribute.
     * Defaults to 1.2 for backwards compatibility.
     *
     * @param DOMDocument $dom
     *
     * @throws \Viserio\Contract\Parser\Exception\InvalidArgumentException;
     *
     * @return string
     */
    public static function getVersionNumber(DOMDocument $dom): string
    {
        /** @var DOMNode $xliff */
        foreach ($dom->getElementsByTagName('xliff') as $xliff) {
            if (($version = $xliff->attributes->getNamedItem('version')) !== null) {
                return $version->nodeValue;
            }

            if (null !== $namespace = $xliff->attributes->getNamedItem('xmlns')) {
                $namespace = $namespace->C14N();

                if (\substr_compare('urn:oasis:names:tc:xliff:document:', $namespace, 0, 34) !== 0) {
                    throw new InvalidArgumentException(\sprintf('Not a valid XLIFF namespace [%s].', $namespace));
                }

                return \substr($namespace, 34);
            }
        }

        return '1.2'; // Falls back to v1.2
    }

    /**
     * Get the right xliff schema from version.
     *
     * @param string $xliffVersion
     *
     * @throws \Viserio\Contract\Parser\Exception\InvalidArgumentException;
     * @throws \Viserio\Contract\Parser\Exception\RuntimeException;
     *
     * @return string
     */
    public static function getSchema(string $xliffVersion): string
    {
        if ($xliffVersion === '1.2') {
            $xmlUri = 'http://www.w3.org/2001/xml.xsd';
            $schemaSource = \dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'schemas' . \DIRECTORY_SEPARATOR . 'xliff-core' . \DIRECTORY_SEPARATOR . 'xliff-core-1.2-strict.xsd';
        } elseif ($xliffVersion === '2.0') {
            $xmlUri = 'informativeCopiesOf3rdPartySchemas/w3c/xml.xsd';
            $schemaSource = \dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'schemas' . \DIRECTORY_SEPARATOR . 'xliff-core' . \DIRECTORY_SEPARATOR . 'xliff-core-2.0.xsd';
        } else {
            throw new InvalidArgumentException(\sprintf('No support implemented for loading XLIFF version [%s].', $xliffVersion));
        }

        \error_clear_last();
        $content = \file_get_contents($schemaSource);

        if ($content === false) {
            $error = \error_get_last();

            throw new RuntimeException($error['message'] ?? 'An error occured', $error['type'] ?? 1);
        }

        return self::fixLocation($content, $xmlUri);
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
        $newPath = \dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'schemas' . \DIRECTORY_SEPARATOR . 'xliff-core' . \DIRECTORY_SEPARATOR . 'xml.xsd';
        $parts = \explode(\DIRECTORY_SEPARATOR, $newPath);

        if (\stripos($newPath, 'phar://') === 0 && ($tmpFile = \tempnam(\sys_get_temp_dir(), 'narrowspark')) !== false) {
            \copy($newPath, $tmpFile);

            $parts = \explode(\DIRECTORY_SEPARATOR, $tmpFile);
        }

        $drive = \PHP_OS_FAMILY === 'Windows' ? \array_shift($parts) . '/' : '';
        $newPath = 'file:///' . $drive . \implode('/', \array_map('rawurlencode', $parts));

        return \str_replace($xmlUri, $newPath, $schemaSource);
    }
}
