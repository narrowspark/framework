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
use DOMElement;
use DOMException;
use DOMNode;
use Viserio\Contract\Parser\Dumper as DumperContract;
use Viserio\Contract\Parser\Exception\DumpException;

class XmlDumper implements DumperContract
{
    /**
     * {@inheritdoc}
     *
     *    array[]
     *        ['root']                           string|array A optional option; String if you like to change the root name or
     *                                                                           array for more options.
     *            ['rootElementName']            string       A optional option; The name of the root element
     *            ['_attributes or @attributes'] array
     *                ['key']                    string
     *        ['version']                        string       A optional option;
     *        ['encoding']                       string       A optional option; to set the file encoding
     *        ['key']                            string|array
     *            ['_attributes or @attributes'] array        A optional option;
     *                ['key']                    string       A optional option;
     *            ['_value or @value']           string       A optional option;
     *            ['_cdata or @cdata']           string       A optional option;
     */
    public function dump(array $data): string
    {
        try {
            $document = new DOMDocument($data['version'] ?? '1.0', $data['encoding'] ?? '');

            if (isset($data['version'])) {
                unset($data['version']);
            }

            if (isset($data['encoding'])) {
                unset($data['encoding']);
            }

            if (\count($data) !== 0 && self::isArrayAllKeySequential($data)) {
                throw new DOMException('Invalid Character Error.');
            }

            $root = $this->createRootElement($document, $data['root'] ?? '');

            if (isset($data['root'])) {
                unset($data['root']);
            }

            $document->appendChild($root);

            $this->convertElement($document, $root, $data);

            return $document->saveXML();
        } catch (DOMException $exception) {
            throw new DumpException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Parse individual element.
     *
     * @param \DOMDocument                        $document
     * @param \DOMElement|DOMNode                 $element
     * @param array<int|string, mixed>|int|string $value
     *
     * @throws DOMException
     *
     * @return void
     */
    private function convertElement(DOMDocument $document, $element, $value): void
    {
        $sequential = self::isArrayAllKeySequential($value);

        if (! \is_array($value)) {
            $element->nodeValue = \is_int($value) ? (string) $value : \htmlspecialchars($value);

            return;
        }

        foreach ($value as $key => $data) {
            if (! $sequential) {
                if ((($key === '_attributes') || ($key === '@attributes')) && $element instanceof DOMElement) {
                    foreach ($data as $attrKey => $attrVal) {
                        $element->setAttribute($attrKey, (string) $attrVal);
                    }
                } elseif ((($key === '_value') || ($key === '@value')) && \is_string($data)) {
                    $element->nodeValue = \htmlspecialchars($data);
                } elseif ((($key === '_cdata') || ($key === '@cdata')) && \is_string($data)) {
                    $element->appendChild($document->createCDATASection($data));
                } else {
                    if (! \is_string($key)) {
                        throw new DOMException('Invalid Character Error.');
                    }

                    $this->addNode($document, $element, $key, $data);
                }
            } elseif (\is_array($data)) {
                $this->addCollectionNode($document, $element, $data);
            } else {
                $this->addSequentialNode($element, $data);
            }
        }
    }

    /**
     * Add node.
     *
     * @param \DOMDocument        $document
     * @param \DOMElement|DOMNode $element
     * @param string              $key
     * @param string|string[]     $value
     *
     * @throws DOMException
     *
     * @return void
     */
    private function addNode(DOMDocument $document, $element, string $key, $value): void
    {
        $key = \str_replace(' ', '_', $key);

        $child = $document->createElement($key);

        $element->appendChild($child);

        $this->convertElement($document, $child, $value);
    }

    /**
     * Add collection node.
     *
     * @param \DOMDocument        $document
     * @param \DOMElement|DOMNode $element
     * @param string|string[]     $value
     *
     * @throws DOMException
     *
     * @return void
     */
    private function addCollectionNode(DOMDocument $document, $element, $value): void
    {
        if ($element->childNodes->length === 0 && $element->attributes->length === 0) {
            $this->convertElement($document, $element, $value);
        }

        $child = $element->cloneNode();
        /** @var DOMNode $parentNode */
        $parentNode = $element->parentNode;
        $parentNode->appendChild($child);

        $this->convertElement($document, $child, $value);
    }

    /**
     * Add sequential node.
     *
     * @param \DOMElement|DOMNode $element
     * @param string              $value
     *
     * @return void
     */
    private function addSequentialNode($element, string $value): void
    {
        if ($element->nodeValue === '' || $element->nodeValue === null) {
            $element->nodeValue = \htmlspecialchars($value);

            return;
        }

        $child = $element->cloneNode();
        $child->nodeValue = \htmlspecialchars($value);

        /** @var DOMNode $parentNode */
        $parentNode = $element->parentNode;
        $parentNode->appendChild($child);
    }

    /**
     * Create the root element.
     *
     * @param DOMDocument                                 $document
     * @param array<string, array<string, string>>|string $rootElement
     *
     * @return DOMElement
     */
    private function createRootElement(DOMDocument $document, $rootElement): DOMElement
    {
        if (\is_string($rootElement)) {
            return $document->createElement($rootElement !== '' ? $rootElement : 'root');
        }

        /** @var string $rootElementName */
        $rootElementName = $rootElement['rootElementName'] ?? 'root';
        $element = $document->createElement($rootElementName);

        foreach ($rootElement as $key => $value) {
            if ($key !== '_attributes' && $key !== '@attributes') {
                continue;
            }

            foreach ($rootElement[$key] as $attrKey => $attrVal) {
                $element->setAttribute($attrKey, $attrVal);
            }
        }

        return $element;
    }

    /**
     * Check if array are all sequential.
     *
     * @param mixed $value
     *
     * @return bool
     */
    private static function isArrayAllKeySequential($value): bool
    {
        if (! \is_array($value)) {
            return false;
        }

        if (\count($value) <= 0) {
            return true;
        }

        return \array_unique(\array_map('\is_int', \array_keys($value))) === [true];
    }
}
