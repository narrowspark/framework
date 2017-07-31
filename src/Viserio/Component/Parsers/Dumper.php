<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers;

use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Component\Contracts\Parsers\Exception\NotSupportedException;
use Viserio\Component\Parsers\Dumper\IniDumper;
use Viserio\Component\Parsers\Dumper\JsonDumper;
use Viserio\Component\Parsers\Dumper\PhpDumper;
use Viserio\Component\Parsers\Dumper\QtDumper;
use Viserio\Component\Parsers\Dumper\QueryStrDumper;
use Viserio\Component\Parsers\Dumper\SerializeDumper;
use Viserio\Component\Parsers\Dumper\XliffDumper;
use Viserio\Component\Parsers\Dumper\XmlDumper;
use Viserio\Component\Parsers\Dumper\YamlDumper;

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
        'text/xml'        => 'xml',
        // Xliff
        'application/x-xliff+xml' => 'xlf',
        // JSON
        'application/json'         => 'json',
        'application/x-javascript' => 'json',
        'text/javascript'          => 'json',
        'text/x-javascript'        => 'json',
        'text/x-json'              => 'json',
        // YAML
        'text/yaml'          => 'yaml',
        'text/x-yaml'        => 'yaml',
        'application/yaml'   => 'yaml',
        'application/x-yaml' => 'yaml',
        // MISC
        'application/vnd.php.serialized'    => 'serialize',
        'application/x-www-form-urlencoded' => 'querystr',
    ];

    /**
     * All supported dumper.
     *
     * @var array
     */
    private static $supportedDumper = [
        'ini'       => IniDumper::class,
        'json'      => JsonDumper::class,
        'php'       => PhpDumper::class,
        'querystr'  => QueryStrDumper::class,
        'serialize' => SerializeDumper::class,
        'ts'        => QtDumper::class,
        'xml'       => XmlDumper::class,
        'xlf'       => XliffDumper::class,
        'yaml'      => YamlDumper::class,
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
     * @param \Viserio\Component\Contracts\Parsers\Dumper $dumper
     * @param string                                      $extension
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
     * @throws \Viserio\Component\Contracts\Parsers\Exception\NotSupportedException
     *
     * @return \Viserio\Component\Contracts\Parsers\Dumper
     */
    public function getDumper(string $type): DumperContract
    {
        if (isset(self::$supportedDumper[$type])) {
            return new self::$supportedDumper[$type]();
        }

        if (isset(self::$supportedMimeTypes[$type])) {
            $class = self::$supportedDumper[self::$supportedMimeTypes[$type]];

            if (\is_object($class)) {
                return $class;
            }

            return new $class();
        }

        throw new NotSupportedException(\sprintf('Given extension or mime type [%s] is not supported.', $type));
    }
}
