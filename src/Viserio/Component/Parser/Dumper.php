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

use Viserio\Component\Parser\Dumper\IniDumper;
use Viserio\Component\Parser\Dumper\JsonDumper;
use Viserio\Component\Parser\Dumper\PhpArrayDumper;
use Viserio\Component\Parser\Dumper\QtDumper;
use Viserio\Component\Parser\Dumper\QueryStrDumper;
use Viserio\Component\Parser\Dumper\SerializeDumper;
use Viserio\Component\Parser\Dumper\XliffDumper;
use Viserio\Component\Parser\Dumper\XmlDumper;
use Viserio\Component\Parser\Dumper\YamlDumper;
use Viserio\Contract\Parser\Dumper as DumperContract;
use Viserio\Contract\Parser\Exception\NotSupportedException;

class Dumper
{
    /**
     * Supported mime type formats.
     *
     * @var array
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
     * All supported dumper.
     *
     * @var array
     */
    private static $supportedDumper = [
        'ini' => IniDumper::class,
        'json' => JsonDumper::class,
        'php' => PhpArrayDumper::class,
        'querystr' => QueryStrDumper::class,
        'serialize' => SerializeDumper::class,
        'ts' => QtDumper::class,
        'xml' => XmlDumper::class,
        'xlf' => XliffDumper::class,
        'yaml' => YamlDumper::class,
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
     * Add a new dumper.
     *
     * @param \Viserio\Contract\Parser\Dumper $dumper
     * @param string                          $extension
     *
     * @return void
     */
    public function addDumper(DumperContract $dumper, string $extension): void
    {
        self::$supportedDumper[$extension] = $dumper;
    }

    /**
     * Dump data in your choosing format.
     *
     * @param array  $data
     * @param string $format
     *
     * @throws \Viserio\Contract\Parser\Exception\DumpException
     * @throws \Viserio\Contract\Parser\Exception\NotSupportedException
     *
     * @return string
     */
    public function dump(array $data, string $format): string
    {
        $dumper = $this->getDumper($format);

        return $dumper->dump($data);
    }

    /**
     * Get supported dumper on extension or mime type.
     *
     * @param string $type
     *
     * @throws \Viserio\Contract\Parser\Exception\NotSupportedException
     *
     * @return \Viserio\Contract\Parser\Dumper
     */
    public function getDumper(string $type): DumperContract
    {
        if (isset(self::$supportedDumper[$type])) {
            return new self::$supportedDumper[$type]();
        }

        if (isset(self::$supportedMimeTypes[$type])) {
            $class = self::$supportedDumper[self::$supportedMimeTypes[$type]];

            if (\is_object($class) && $class instanceof DumperContract) {
                return $class;
            }

            return new $class();
        }

        throw new NotSupportedException(\sprintf('Given extension or mime type [%s] is not supported.', $type));
    }
}
