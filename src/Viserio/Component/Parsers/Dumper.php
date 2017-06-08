<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers;

use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Component\Contracts\Parsers\Exception\NotSupportedException;
use Viserio\Component\Parsers\Formats\Ini;
use Viserio\Component\Parsers\Formats\Json;
use Viserio\Component\Parsers\Formats\Php;
use Viserio\Component\Parsers\Formats\Po;
use Viserio\Component\Parsers\Formats\Qt;
use Viserio\Component\Parsers\Formats\QueryStr;
use Viserio\Component\Parsers\Formats\Serialize;
use Viserio\Component\Parsers\Formats\Xliff;
use Viserio\Component\Parsers\Formats\Xml;
use Viserio\Component\Parsers\Formats\Yaml;
use Viserio\Component\Parsers\Traits\GuessFormatTrait;

final class Dumper extends AbstractFormatter
{
    use GuessFormatTrait;

    /**
     * Supported mime type formats.
     *
     * @var array
     */
    private $supportedFormats = [
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

    private $supportedDumper = [
        'ini'       => Ini::class,
        'json'      => Json::class,
        'php'       => Php::class,
        'po'        => Po::class,
        'querystr'  => QueryStr::class,
        'serialize' => Serialize::class,
        'ts'        => Qt::class,
        'xml'       => Xml::class,
        'xlf'       => Xliff::class,
        'yaml'      => Yaml::class,
    ];

    /**
     * Dump given data.
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
     * {@inheritdoc}
     */
    public function getDumper(string $type): DumperContract
    {
        if (isset($this->supportedDumper[$type])) {
            return new $this->supportedDumper[$type]();
        } elseif (isset($this->supportedFormats[$type])) {
            return new $this->supportedDumper[$this->supportedFormats[$type]]();
        }

        throw new NotSupportedException(sprintf('Format [%s] from string/file is not supported.', $type));
    }
}
