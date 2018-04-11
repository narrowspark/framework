<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Traits;

use DOMDocument;
use Viserio\Component\Contract\Translation\Exception\InvalidArgumentException;

trait GetXliffVersionNumberTrait
{
    /**
     * Gets xliff file version based on the root "version" attribute.
     * Defaults to 1.2 for backwards compatibility.
     *
     * @param \DOMDocument $dom
     *
     * @throws \Viserio\Component\Contract\Translation\Exception\InvalidArgumentException;
     *
     * @return string
     */
    protected static function getXliffVersionNumber(DOMDocument $dom): string
    {
        /** @var \DOMNode $xliff */
        foreach ($dom->getElementsByTagName('xliff') as $xliff) {
            if ($version = $xliff->attributes->getNamedItem('version')) {
                return $version->nodeValue;
            }

            if ($namespace = $xliff->namespaceURI) {
                if (\substr_compare('urn:oasis:names:tc:xliff:document:', $namespace, 0, 34) !== 0) {
                    throw new InvalidArgumentException(\sprintf('Not a valid XLIFF namespace "%s"', $namespace));
                }

                return \mb_substr($namespace, 34);
            }
        }

        return '1.2'; // Falls back to v1.2
    }
}
