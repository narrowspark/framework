<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Traits;

use Viserio\Component\Contract\Parser\Exception\InvalidArgumentException;

trait GetXliffSchemaTrait
{
    /**
     * Fix directory separators for windows, linux and normalize path.
     *
     * @param array|string $paths
     *
     * @return array|string
     */
    abstract protected static function normalizeDirectorySeparator($paths);

    /**
     * Get the right xliff schema from version.
     *
     * @param string $xliffVersion
     *
     * @throws \Viserio\Component\Contract\Parser\Exception\InvalidArgumentException;
     *
     * @return string
     */
    protected static function getXliffSchema(string $xliffVersion): string
    {
        if ($xliffVersion === '1.2') {
            $xmlUri       = 'http://www.w3.org/2001/xml.xsd';
            $schemaSource = self::normalizeDirectorySeparator(__DIR__ . '/../Schemas/xliff-core/xliff-core-1.2-strict.xsd');
        } elseif ($xliffVersion === '2.0') {
            $xmlUri       = 'informativeCopiesOf3rdPartySchemas/w3c/xml.xsd';
            $schemaSource = self::normalizeDirectorySeparator(__DIR__ . '/../Schemas/xliff-core/xliff-core-2.0.xsd');
        } else {
            throw new InvalidArgumentException(\sprintf('No support implemented for loading XLIFF version [%s].', $xliffVersion));
        }

        return self::fixXmlLocation(\file_get_contents($schemaSource), $xmlUri);
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
        $newPath = \str_replace('\\', '/', \dirname(__DIR__) . '/Schemas/xliff-core/xml.xsd');
        $parts   = \explode('/', $newPath);

        if (\mb_stripos($newPath, 'phar://') === 0) {
            if ($tmpfile = \tempnam(\sys_get_temp_dir(), 'narrowspark')) {
                \copy($newPath, $tmpfile);
                $parts = \explode('/', \str_replace('\\', '/', $tmpfile));
            }
        }

        $drive   = '\\' === DIRECTORY_SEPARATOR ? \array_shift($parts) . '/' : '';
        $newPath = 'file:///' . $drive . \implode('/', \array_map('rawurlencode', $parts));

        return \str_replace($xmlUri, $newPath, $schemaSource);
    }
}
