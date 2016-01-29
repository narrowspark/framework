<?php
namespace Viserio\Filesystem\Parsers;

use League\Flysystem\FileNotFoundException;
use Viserio\Contracts\Filesystem\Exception\LoadingException;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Contracts\Filesystem\Parser as ParserContract;
use Viserio\Filesystem\Parsers\Traits\IsGroupTrait;
use Yosymfony\Toml\Toml;

class TomlParser implements ParserContract
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
     * @param \Viserio\Contracts\Filesystem\Filesystem $files
     */
    public function __construct(FilesystemContract $files)
    {
        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($filename, $group = null)
    {
        if (!class_exists('Yosymfony\\Toml\\Toml')) {
            throw new \RuntimeException('Unable to read toml, the Toml Parser is not installed.');
        }

        if ($this->files->has($filename)) {
            $data = Toml::Parse($filename);

            if ($group !== null) {
                return $this->isGroup($group, (array) $data);
            }

            return (array) $data;
        }

        throw new FileNotFoundException($filename);
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
        return (bool) preg_match('/(\.toml)(\.dist)?/', $filename);
    }

    /**
     * Format a toml file for saving. [NOT IMPLEMENTED].
     *
     * @param array $data data
     *
     * @return string data export
     */
    public function dump(array $data)
    {
        return '';
    }
}
