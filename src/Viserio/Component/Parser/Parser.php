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

namespace Viserio\Component\Parser;

use finfo;
use Viserio\Component\Parser\Parser\IniParser;
use Viserio\Component\Parser\Parser\JsonParser;
use Viserio\Component\Parser\Parser\PhpArrayParser;
use Viserio\Component\Parser\Parser\PoParser;
use Viserio\Component\Parser\Parser\QtParser;
use Viserio\Component\Parser\Parser\QueryStrParser;
use Viserio\Component\Parser\Parser\SerializeParser;
use Viserio\Component\Parser\Parser\TomlParser;
use Viserio\Component\Parser\Parser\XliffParser;
use Viserio\Component\Parser\Parser\XmlParser;
use Viserio\Component\Parser\Parser\YamlParser;
use Viserio\Contract\Parser\Exception\NotSupportedException;
use Viserio\Contract\Parser\Exception\RuntimeException;
use Viserio\Contract\Parser\Parser as ParserContract;

class Parser
{
    /**
     * Supported mime type formats.
     *
     * @var array<string, string>
     */
    private static $supportedMimeTypes = [
        // XML
        'application/xml' => 'xml',
        'text/xml' => 'xml',
        // Xliff
        'application/x-xliff+xml' => 'xlf',
        // JSON
        'application/json' => 'json',
        'application/x-javascript' => 'json',
        'text/javascript' => 'json',
        'text/x-javascript' => 'json',
        'text/x-json' => 'json',
        // YAML
        'text/yaml' => 'yaml',
        'text/x-yaml' => 'yaml',
        'application/yaml' => 'yaml',
        'application/x-yaml' => 'yaml',
        // MISC
        'application/vnd.php.serialized' => 'serialize',
        'application/x-www-form-urlencoded' => 'querystr',
    ];

    /**
     * All supported parser.
     *
     * @var array<string, string|\Viserio\Contract\Parser\Parser>
     */
    private static $supportedParsers = [
        'ini' => IniParser::class,
        'json' => JsonParser::class,
        'php' => PhpArrayParser::class,
        'po' => PoParser::class,
        'querystr' => QueryStrParser::class,
        'serialize' => SerializeParser::class,
        'toml' => TomlParser::class,
        'ts' => QtParser::class,
        'xml' => XmlParser::class,
        'xlf' => XliffParser::class,
        'yaml' => YamlParser::class,
        'yml' => YamlParser::class,
    ];

    /**
     * Add a new mime type with extension.
     *
     * @param string $mimeType
     * @param string $extension
     *
     * @return void
     */
    public function addMimeType(string $mimeType, string $extension): void
    {
        self::$supportedMimeTypes[$mimeType] = $extension;
    }

    /**
     * Add a new parser.
     *
     * @param \Viserio\Contract\Parser\Parser $parser
     * @param string                          $extension
     *
     * @return void
     */
    public function addParser(ParserContract $parser, string $extension): void
    {
        self::$supportedParsers[$extension] = $parser;
    }

    /**
     * Parse given file path or content string.
     *
     * @param string $payload
     *
     * @throws \Viserio\Contract\Parser\Exception\RuntimeException      if an error occurred during reading
     * @throws \Viserio\Contract\Parser\Exception\NotSupportedException if a mime type is not supported
     * @throws \Viserio\Contract\Parser\Exception\ParseException
     *
     * @return array<int|string, mixed>
     */
    public function parse(string $payload): array
    {
        if ($payload === '') {
            return [];
        }

        $format = $this->getFormat($payload);

        if ($format !== 'php' && \is_file($payload)) {
            $payload = \file_get_contents($payload);

            if ($payload === false) {
                throw new RuntimeException(\sprintf('A error occurred during reading [%s].', $payload));
            }
        }

        return $this->getParser($format)->parse($payload);
    }

    /**
     * Get supported parser on extension or mime type.
     *
     * @param string $type
     *
     * @throws \Viserio\Contract\Parser\Exception\NotSupportedException
     *
     * @return \Viserio\Contract\Parser\Parser
     */
    public function getParser(string $type): ParserContract
    {
        if (isset(self::$supportedParsers[$type])) {
            return new self::$supportedParsers[$type]();
        }

        if (isset(self::$supportedMimeTypes[$type])) {
            $class = self::$supportedParsers[self::$supportedMimeTypes[$type]];

            if (\is_string($class)) {
                return new $class();
            }

            return $class;
        }

        throw new NotSupportedException(\sprintf('Given extension or mime type [%s] is not supported.', $type));
    }

    /**
     * Auto detect the payload data type using finfo and pathinfo.
     *
     * @param string $payload
     *
     * @return string Tries to return the short format code (xml, json, ...) else the mime type
     */
    protected function getFormat(string $payload): string
    {
        if (\is_file($file = $payload)) {
            $format = \pathinfo($file, \PATHINFO_EXTENSION);
        } else {
            // try if content is json
            \json_decode($payload);

            if (\json_last_error() === \JSON_ERROR_NONE) {
                return 'json';
            }

            $format = (new finfo(\FILEINFO_MIME_TYPE))->buffer($payload);
        }

        return self::$supportedMimeTypes[$format] ?? (string) $format;
    }
}
