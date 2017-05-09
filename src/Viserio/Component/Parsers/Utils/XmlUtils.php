<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Utils;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMText;
use InvalidArgumentException;
use Throwable;

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
     * Loads an XML file.
     *
     * @param string               $file             An XML file path
     * @param string|callable|null $schemaOrCallable An XSD schema file path, a callable, or null to disable validation
     *
     * @throws \InvalidArgumentException When loading of XML file returns error
     *
     * @return \DOMDocument
     */
    public static function loadFile(string $file, $schemaOrCallable = null): DOMDocument
    {
        if (! file_exists($file)) {
            throw new InvalidArgumentException(sprintf('No such file %s.', $file));
        }

        return self::loadString(@file_get_contents($file), $schemaOrCallable);
    }

    /**
     * Loads an XML string.
     *
     * @param string               $content             An XML string content
     * @param string|callable|null $schemaOrCallable An XSD schema file path, a callable, or null to disable validation
     *
     * @throws \InvalidArgumentException When loading of XML file returns error
     *
     * @return \DOMDocument
     */
    public static function loadString(string $content, $schemaOrCallable = null): DOMDocument
    {
        if (trim($content) === '') {
            throw new InvalidArgumentException('Content does not contain valid XML, it is empty.');
        }

        $internalErrors  = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);

        libxml_clear_errors();

        $dom                  = new DOMDocument();
        $dom->validateOnParse = true;

        if (! $dom->loadXML($content, LIBXML_NONET | (defined('LIBXML_COMPACT') ? LIBXML_COMPACT : 0))) {
            libxml_disable_entity_loader($disableEntities);

            throw new InvalidArgumentException(implode("\n", self::getXmlErrors($internalErrors)));
        }

        $dom->normalizeDocument();

        libxml_use_internal_errors($internalErrors);
        libxml_disable_entity_loader($disableEntities);

        foreach ($dom->childNodes as $child) {
            if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                throw new InvalidArgumentException('Document types are not allowed.');
            }
        }

        if ($schemaOrCallable !== null) {
            self::validateXmlDom($dom, $schemaOrCallable);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);

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
     * @param \DomElement $element     A \DomElement instance
     * @param bool        $checkPrefix Check prefix in an element or an attribute name
     *
     * @return array|string|null A PHP array
     */
    public static function convertDomElementToArray(DOMElement $element, bool $checkPrefix = true)
    {
        $prefix = (string) $element->prefix;
        $empty  = true;
        $config = [];

        foreach ($element->attributes as $name => $node) {
            if ($checkPrefix && ! in_array((string) $node->prefix, ['', $prefix], true)) {
                continue;
            }

            $config[$name] = self::phpize($node->value);
            $empty         = false;
        }

        $nodeValue = false;

        foreach ($element->childNodes as $node) {
            if ($node instanceof DOMText) {
                if (trim($node->nodeValue) !== '') {
                    $nodeValue = trim($node->nodeValue);
                    $empty     = false;
                }
            } elseif ($checkPrefix && $prefix != (string) $node->prefix) {
                continue;
            } elseif (! $node instanceof DOMComment) {
                $value = self::convertDomElementToArray($node, $checkPrefix);
                $key   = $node->localName;

                if (isset($config[$key])) {
                    if (! is_array($config[$key]) || ! is_int(key($config[$key]))) {
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

            if (count($config)) {
                $config['value'] = $value;
            } else {
                $config = $value;
            }
        }

        return ! $empty ? $config : null;
    }

    /**
     * Converts an xml value to a PHP type.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public static function phpize($value)
    {
        $value          = (string) $value;
        $lowercaseValue = mb_strtolower($value);

        switch (true) {
            case 'null' === $lowercaseValue:
                return;
            case ctype_digit($value):
                $raw  = $value;
                $cast = (int) $value;

                return '0' == $value[0] ? octdec($value) : (((string) $raw === (string) $cast) ? $cast : $raw);
            case isset($value[1]) && '-' === $value[0] && ctype_digit(mb_substr($value, 1)):
                $raw  = $value;
                $cast = (int) $value;

                return '0' == $value[1] ? octdec($value) : (((string) $raw === (string) $cast) ? $cast : $raw);
            case 'true' === $lowercaseValue:
                return true;
            case 'false' === $lowercaseValue:
                return false;
            case isset($value[1]) && '0b' == $value[0] . $value[1]:
                return bindec($value);
            case is_numeric($value):
                return '0x' === $value[0] . $value[1] ? hexdec($value) : (float) $value;
            case preg_match('/^0x[0-9a-f]++$/i', $value):
                return hexdec($value);
            case preg_match('/^(-|\+)?[0-9]+(\.[0-9]+)?$/', $value):
                return (float) $value;
            default:
                return $value;
        }
    }

    /**
     * @param mixed $schemaOrCallable
     *
     * @return bool
     */
    private static function validateXmlDom(DOMDocument $dom, $schemaOrCallable)
    {
        $internalErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $exception = null;

        if (is_callable($schemaOrCallable)) {
            try {
                $valid = call_user_func($schemaOrCallable, $dom, $internalErrors);
            } catch (Throwable $exception) {
                $valid = false;
            }
        } elseif (! is_array($schemaOrCallable) && is_file((string) $schemaOrCallable)) {
            $schemaSource = file_get_contents((string) $schemaOrCallable);
            $valid        = @$dom->schemaValidateSource($schemaSource);
        } else {
            libxml_use_internal_errors($internalErrors);

            throw new InvalidArgumentException('The schemaOrCallable argument has to be a valid path to XSD file or callable.');
        }

        if (! $valid) {
            $messages = self::getXmlErrors($internalErrors);

            if (empty($messages)) {
                $messages = ['The XML file is not valid.'];
            }

            throw new InvalidArgumentException(implode("\n", $messages), 0, $exception);
        }
    }

    /**
     * @var bool
     *
     * @return array
     */
    private static function getXmlErrors(bool $internalErrors): array
    {
        $errors = [];

        foreach (libxml_get_errors() as $error) {
            $errors[] = sprintf('[%s %s] %s (in %s - line %d, column %d)',
                $error->level == LIBXML_ERR_WARNING ? 'WARNING' : 'ERROR',
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
}
