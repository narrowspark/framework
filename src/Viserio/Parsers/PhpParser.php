<?php
namespace Viserio\Parsers;

use League\Flysystem\FileNotFoundException;
use Viserio\Contracts\Filesystem\Exception\LoadingException;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Contracts\Filesystem\Parser as ParserContract;

class PhpParser implements ParserContract
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
     * @param \Viserio\Contracts\Filesystem\Filesystem $files
     */
    public function __construct(FilesystemContract $files)
    {
        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($filename)
    {
        if ($this->files->has($filename)) {
            return (array) $this->files->getRequire($filename);
        }

        throw new FileNotFoundException($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($filename)
    {
        return (bool) preg_match('/\.php/', $filename);
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data)
    {
        $data = var_export($data, true);

        $formatted = str_replace(
            ['  ', '['],
            ["\t", '['],
            $data
        );

        $output = <<<CONF
<?php

return {$formatted};
CONF;

        return $output;
    }
}
