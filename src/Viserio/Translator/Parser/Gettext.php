<?php
namespace Viserio\Translator\Parser;

use Viserio\Contracts\Filesystem\LoadingException;
use Viserio\Contracts\Filesystem\Parser as ParserContract;
use Viserio\Filesystem\Filesystem;
use Viserio\Filesystem\Parser\Traits\IsGroupTrait;

class Gettext implements ParserContract
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
     * Loads a file and gets its' contents as an array.
     *
     * @param string      $filename
     * @param string|null $group
     *
     * @return array|string|null
     */
    public function load($filename, $group = null)
    {

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

    }

    /**
     * Format a data file for saving.
     *
     * @param array $data data
     *
     * @return string|false data export
     */
    public function format(array $data)
    {

    }
}
