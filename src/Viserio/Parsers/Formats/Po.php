<?php
namespace Viserio\Parsers\Formats;

use Exception;
use Sepia\FileHandler;
use Sepia\PoParser;
use Viserio\Contracts\Filesystem\LoadingException;
use Viserio\Contracts\Parsers\Format as FormatContract;
use Viserio\Filesystem\Filesystem;

class Po implements FormatContract
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
        if (!class_exists('Sepia\\PoParser')) {
            throw new LogicException('Loading translations from the Po format requires the Sepia PoParser component.');
        }

        try {
            if ($this->files->exists($filename)) {
                return (new PoParser())->parseFile($filename);
            }
        } catch (Exception $exception) {
            throw new LoadingException(sprintf('Unable to parse the Mo string: [%s]', $exception->getMessage()));
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
