<?php
namespace Viserio\Translator\Parsers;

use Exception;
use Viserio\Contracts\Filesystem\LoadingException;
use Viserio\Contracts\Filesystem\Parser as ParserContract;
use Viserio\Filesystem\Filesystem;

class Mo implements ParserContract
{
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
     * Loads a MO file and gets its' contents as an array.
     *
     * @param string      $filename
     * @param string|null $group
     *
     * @throws \LoadingException
     *
     * @return array|string|null
     */
    public function load($filename, $group = null)
    {
        try {
            if ($this->files->exists($filename)) {
                return '';
            }
        } catch (Exception $exception) {
            throw new LoadingException(sprintf('Unable to parse the Mo string: [%s]', $exception->getMessage()));
        }
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
        return (bool) preg_match('#\.mo?$#', $filename);
    }

    /**
     * Format a MO file for saving.
     *
     * @param array $data data
     *
     * @return string data export
     */
    public function format(array $data)
    {
        //
    }
}
