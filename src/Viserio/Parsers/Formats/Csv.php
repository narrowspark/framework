<?php
namespace Viserio\Parsers\Formats;

use Exception;
use Viserio\Contracts\Filesystem\LoadingException;
use Viserio\Contracts\Parsers\Format as FormatContract;
use Viserio\Filesystem\Filesystem;

class Csv implements FormatContract
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
     * {@inheritdoc}
     */
    public function parse($payload)
    {
        try {
            if ($this->files->exists($filename)) {
                return '';
            }
        } catch (Exception $exception) {
            throw new LoadingException(sprintf('Unable to parse the Csv string: [%s]', $exception->getMessage()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data)
    {
        //
    }
}
