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

namespace Viserio\Component\Parser\Utils;

use DOMDocument;
use DOMElement;
use DOMText;
use SimpleXMLElement;
use Throwable;
use Viserio\Contract\Parser\Exception\FileNotFoundException;
use Viserio\Contract\Parser\Exception\InvalidArgumentException;
use Viserio\Contract\Parser\Exception\ParseException;
use Viserio\Contract\Parser\Exception\RuntimeException;
use function key;
use function simplexml_import_dom;

/**
 * This file has been ported from Symfony. The original
 * code is (c) Fabien Potencier <fabien@symfony.com>.
 */
final class XmlUtils
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
     * A simplexml_import_dom wrapper.
     *
     * @throws \Viserio\Contract\Parser\Exception\ParseException
     */
    public static function importDom(DOMDocument $dom): SimpleXMLElement
    {
        $xml = \simplexml_import_dom($dom);

        if ($xml === false) {
            throw new ParseException('A failure happend on importing a DOMDocument.');
        }

        return $xml;
    }

    /**
     * Validates and parses the given file into a DOMDocument.
     *
     * @param string $schema source of the schema
     *
     * @throws \Viserio\Contract\Parser\Exception\InvalidArgumentException
     *
     * @return array<int, array<string, int|string>>
     */
    public static function validateSchema(DOMDocument $dom, string $schema): array
    {
        $internalErrors = \libxml_use_internal_errors(true);
        $disableEntities = \libxml_disable_entity_loader(false);
        $isValid = @$dom->schemaValidateSource($schema);

        if (! $isValid) {
            \libxml_disable_entity_loader($disableEntities);

            return self::getXmlErrors($internalErrors);
        }

        \libxml_disable_entity_loader($disableEntities);

        $dom->normalizeDocument();

        \libxml_clear_errors();
        \libxml_use_internal_errors($internalErrors);

        return [];
    }

    /**
     * Transforms xml errors to errors string.
     *
     * @param array<int, array<string, int|string>> $xmlErrors
     */
    public static function getErrorsAsString(array $xmlErrors): string
    {
        $errorsAsString = '';

        foreach ($xmlErrors as $error) {
            $errorsAsString .= \sprintf(
                "[%s %s] %s (in %s - line %d, column %d)\n",
                \LIBXML_ERR_WARNING === $error['level'] ? 'WARNING' : 'ERROR',
                $error['code'],
                $error['message'],
                $error['file'],
                $error['line'],
                $error['column']
            );
        }

        return $errorsAsString;
    }

    /**
     * Returns the XML errors of the internal XML parser.
     *
     * @return array<int, array<string, int|string>> An array of errors
     */
    public static function getXmlErrors(bool $internalErrors): array
    {
        $errors = [];

        foreach (\libxml_get_errors() as $error) {
            $errors[] = [
                'level' => $error->level === \LIBXML_ERR_WARNING ? 'WARNING' : 'ERROR',
                'code' => $error->code,
                'message' => \trim($error->message),
                'file' => $error->file ?? 'n/a',
                'line' => $error->line,
                'column' => $error->column,
            ];
        }

        \libxml_clear_errors();
        \libxml_use_internal_errors($internalErrors);

        return $errors;
    }

    /**
     * Loads an XML file.
     *
     * @param string               $file             An XML file path
     * @param null|callable|string $schemaOrCallable An XSD schema file path, a callable, or null to disable validation
     *
     * @throws \Viserio\Contract\Parser\Exception\InvalidArgumentException When loading of XML file returns error
     * @throws \Viserio\Contract\Parser\Exception\FileNotFoundException
     */
    public static function loadFile(string $file, $schemaOrCallable = null): DOMDocument
    {
        if (! \file_exists($file)) {
            throw new FileNotFoundException(\sprintf('No such file [%s] found.', $file));
        }

        return self::loadString((string) \file_get_contents($file), $schemaOrCallable);
    }

    /**
     * Loads an XML string.
     *
     * @param string               $content          An XML string content
     * @param null|callable|string $schemaOrCallable An XSD schema file path, a callable, or null to disable validation
     *
     * @throws \Viserio\Contract\Parser\Exception\InvalidArgumentException When loading of XML file returns error
     */
    public static function loadString(string $content, $schemaOrCallable = null): DOMDocument
    {
        if (\trim($content) === '') {
            throw new InvalidArgumentException('Content does not contain valid XML, it is empty.');
        }

        $internalErrors = \libxml_use_internal_errors(true);
        $disableEntities = \libxml_disable_entity_loader();

        \libxml_clear_errors();

        $dom = new DOMDocument();
        $dom->validateOnParse = true;

        if ($dom->loadXML($content, \LIBXML_NONET | (\defined('LIBXML_COMPACT') ? \LIBXML_COMPACT : 0)) === false) {
            \libxml_disable_entity_loader($disableEntities);

            if (\count($errors = XliffUtils::validateSchema($dom)) !== 0) {
                throw new InvalidArgumentException(self::getErrorsAsString($errors));
            }
        }

        $dom->normalizeDocument();

        \libxml_use_internal_errors($internalErrors);
        \libxml_disable_entity_loader($disableEntities);

        foreach ($dom->childNodes as $child) {
            if ($child->nodeType === \XML_DOCUMENT_TYPE_NODE) {
                throw new InvalidArgumentException('Document types are not allowed.');
            }
        }

        if ($schemaOrCallable !== null) {
            self::validateXmlDom($dom, $schemaOrCallable);
        }

        \libxml_clear_errors();
        \libxml_use_internal_errors($internalErrors);

        return $dom;
    }

    /**
     * Converts a \DomElement object to a PHP array.
     *
     * The following rules applies during the conversion:
     *
     *  * Each tag is converted to a key value or an array
     *    if there is more than one "value"
     *
     *  * The content of a tag is set under a "value" key (<foo>bar</foo>)
     *    if the tag also has some nested tags
     *
     *  * The attributes are converted to keys (<foo foo="bar"/>)
     *
     *  * The nested-tags are converted to keys (<foo><foo>bar</foo></foo>)
     *
     * @param DOMElement $element     A \DomElement instance
     * @param bool       $checkPrefix Check prefix in an element or an attribute name
     *
     * @return null|array<int|string, mixed>|string A PHP array
     */
    public static function convertDomElementToArray(DOMElement $element, bool $checkPrefix = true)
    {
        $prefix = $element->prefix;
        $empty = true;
        $config = [];

        foreach ($element->attributes as $name => $node) {
            if ($checkPrefix && ! \in_array((string) $node->prefix, ['', $prefix], true)) {
                continue;
            }

            $config[$name] = self::phpize($node->value);
            $empty = false;
        }

        $nodeValue = false;

        foreach ($element->childNodes as $node) {
            if ($node instanceof DOMText) {
                if (\trim($node->nodeValue) !== '') {
                    $nodeValue = \trim($node->nodeValue);
                    $empty = false;
                }
            } elseif ($checkPrefix && $prefix !== $node->prefix) {
                continue;
            } elseif ($node instanceof DOMElement) {
                $value = self::convertDomElementToArray($node, $checkPrefix);
                $key = $node->localName;

                if (isset($config[$key])) {
                    if (! \is_array($config[$key]) || ! \is_int(\key($config[$key]))) {
                        $config[$key] = [$config[$key]];
                    }

                    $config[$key][] = $value;
                } else {
                    $config[$key] = $value;
                }

                $empty = false;
            }
        }

        if ($nodeValue !== false) {
            $value = self::phpize($nodeValue);

            if (\count($config) !== 0) {
                $config['value'] = $value;
            } else {
                $config = $value;
            }
        }

        return ! $empty ? $config : null;
    }

    /**
     * Converts an xml value to a PHP type.
     */
    public static function phpize($value)
    {
        $value = (string) $value;
        $lowercaseValue = \strtolower($value);

        if ('null' === $lowercaseValue) {
            return;
        }

        if (\ctype_digit($value)) {
            return self::transformToNumber($value, 0);
        }

        if (isset($value[1]) && '-' === $value[0] && \ctype_digit(\substr($value, 1))) {
            return self::transformToNumber($value, 1);
        }

        if ($lowercaseValue === 'true') {
            return true;
        }

        if ('false' === $lowercaseValue) {
            return false;
        }

        if (isset($value[1]) && '0b' === $value[0] . $value[1]) {
            return \bindec($value);
        }

        if (\is_numeric($value)) {
            return '0x' === $value[0] . $value[1] ? \hexdec($value) : (float) $value;
        }

        if (\preg_match('/^0x[0-9a-f]++$/i', $value) === 1) {
            return \hexdec($value);
        }

        if (\preg_match('/^(-|\+)?\d+(\.\d+)?$/', $value) === 1) {
            return (float) $value;
        }

        return $value;
    }

    /**
     * Validates DOMDocument against a file or callback.
     *
     * @param mixed $schemaOrCallable should be a callable or a string
     *
     * @throws \Viserio\Contract\Parser\Exception\InvalidArgumentException
     */
    private static function validateXmlDom(DOMDocument $dom, $schemaOrCallable): void
    {
        $internalErrors = \libxml_use_internal_errors(true);
        \libxml_clear_errors();

        $exception = null;

        if (\is_callable($schemaOrCallable)) {
            try {
                $valid = $schemaOrCallable($dom, $internalErrors);
            } catch (Throwable $exception) {
                $valid = false;
            }
        } elseif (\is_string($schemaOrCallable) && \is_file($schemaOrCallable)) {
            \error_clear_last();
            $content = \file_get_contents($schemaOrCallable);

            if ($content === false) {
                $error = \error_get_last();

                throw new RuntimeException($error['message'] ?? 'An error occured', $error['type'] ?? 1);
            }

            $valid = @$dom->schemaValidateSource($content);
        } else {
            \libxml_use_internal_errors($internalErrors);

            throw new InvalidArgumentException('The schemaOrCallable argument has to be a valid path to XSD file or callable.');
        }

        if (! $valid) {
            $errors = self::getErrorsAsString(self::getXmlErrors($internalErrors));

            if ($errors === '') {
                $errors = 'The XML file is not valid.';
            }

            throw new InvalidArgumentException($errors, 0, $exception);
        }
    }

    private static function transformToNumber(string $value, int $position): int
    {
        $raw = $value;
        $cast = (int) $value;

        if ($raw === (string) $cast) {
            return $value[$position] === '0' ? \octdec($value) : $cast;
        }

        return $value[$position] === '0' ? \octdec($value) : (int) $raw;
    }
}
