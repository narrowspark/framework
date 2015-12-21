<?php
namespace Viserio\Filesystem\Parser;

use Viserio\Contracts\Filesystem\LoadingException;
use Viserio\Contracts\Filesystem\Parser as ParserContract;
use Viserio\Filesystem\Filesystem;
use Viserio\Filesystem\Parser\Traits\IsGroupTrait;
use Yosymfony\Toml\Toml as TomlParser;

class Toml implements ParserContract
{
    use IsGroupTrait;

    /**
     * The filesystem instance.
     *
     * @var \Viserio\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new file filesystem loader.
     *
     * @param \Viserio\Filesystem\Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Loads a TOML file and gets its' contents as an array.
     *
     * @param string      $filename
     * @param string|null $group
     *
     * @throws \Exception
     *
     * @return array|string|null
     */
    public function load($filename, $group = null)
    {
        if (!class_exists('Yosymfony\\Toml\\Toml;')) {
            throw new \RuntimeException('Unable to read toml, the Toml Parser is not installed.');
        }

        if ($this->files->exists($filename)) {
            $data = TomlParser::Parse($filename);

            if ($group !== null) {
                return $this->isGroup($group, (array) $data);
            }

            return $data;
        }

        throw new LoadingException('Unable to load config ' . $filename);
    }

    /**
     * Checking if file ist supported.
     *
     * @param string $filename
     *
     * @return bool
     */
    public function supports($filename)
    {
        return (bool) preg_match('#\.toml(\.dist)?$#', $filename);
    }

    /**
     * Format a toml file for saving. [NOT IMPLEMENTED].
     *
     * @param array $data data
     *
     * @throws \Exception
     *
     * @return string data export
     */
    public function format(array $data)
    {
        throw new \Exception('Toml export is not available right now.');
    }
}
