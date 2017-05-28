<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers;

use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Viserio\Component\Contracts\Parsers\Exception\NotSupportedException;
use Viserio\Component\Contracts\Parsers\Format as FormatContract;
use Viserio\Component\Contracts\Parsers\Parser as ParserContract;
use Viserio\Component\Parsers\Formats\Ini;
use Viserio\Component\Parsers\Formats\Json;
use Viserio\Component\Parsers\Formats\Php;
use Viserio\Component\Parsers\Formats\Po;
use Viserio\Component\Parsers\Formats\Qt;
use Viserio\Component\Parsers\Formats\QueryStr;
use Viserio\Component\Parsers\Formats\Serialize;
use Viserio\Component\Parsers\Formats\Toml;
use Viserio\Component\Parsers\Formats\Xliff;
use Viserio\Component\Parsers\Formats\Xml;
use Viserio\Component\Parsers\Formats\Yaml;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class Parser implements ParserContract
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * A server request instance.
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $serverRequest;

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

    /**
     * Supported file formats.
     *
     * @var array
     */
    private $supportedFileFormats = [
        'ini',
        'json',
        'php',
        'po',
        'toml',
        'ts',
        'xml',
        'xlf',
        'yaml',
    ];

    private $supportedParsers = [
        'ini'       => Ini::class,
        'json'      => Json::class,
        'php'       => Php::class,
        'po'        => Po::class,
        'querystr'  => QueryStr::class,
        'serialize' => Serialize::class,
        'toml'      => Toml::class,
        'ts'        => Qt::class,
        'xml'       => Xml::class,
        'xlf'       => Xliff::class,
        'yaml'      => Yaml::class,
    ];

    /**
     * Set a server request instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     *
     * @return void
     */
    public function setServerRequest(ServerRequestInterface $serverRequest): void
    {
        $this->serverRequest = $serverRequest;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormat(?string $format = null): ?string
    {
        if ($format !== null) {
            $format = mb_strtolower($format);
            $format = self::normalizeDirectorySeparator($format);

            if (is_file($format)) {
                return pathinfo($format, PATHINFO_EXTENSION);
            }
        }

        if ($this->serverRequest !== null &&
            $this->serverRequest->hasHeader('content-type')
        ) {
            return $this->serverRequest->getHeader('content-type')[0];
        }

        return $format;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        if ($payload === '') {
            return [];
        }

        $format = $this->getFormat($payload);

        if ($format !== 'php') {
            $fileName = self::normalizeDirectorySeparator($payload);

            if (is_file($fileName)) {
                $payload  = file_get_contents($fileName);

                if ($payload === false) {
                    throw new RuntimeException(sprintf('A error occurred during reading [%s]', $fileName));
                }
            }
        }

        return $this->getParser($format)->parse($payload);
    }

    /**
     * {@inheritdoc}
     */
    public function getParser(string $type): FormatContract
    {
        $supportedFileFormats = array_flip($this->supportedFileFormats);

        if (isset($supportedFileFormats[$type])) {
            return new $this->supportedParsers[$type]();
        } elseif (isset($this->supportedFormats[$type])) {
            return new $this->supportedParsers[$this->supportedFormats[$type]]();
        }

        throw new NotSupportedException(sprintf('Format [%s] from string/file is not supported.', $type));
    }
}
