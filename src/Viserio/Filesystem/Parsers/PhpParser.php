<?php
namespace Viserio\Filesystem\Parser;

use Viserio\Contracts\Filesystem\Exception\LoadingException;
use Viserio\Contracts\Filesystem\Parser as ParserContract;
use Viserio\Filesystem\Filesystem;
use Viserio\Filesystem\Parser\Traits\IsGroupTrait;

class Php implements ParserContract
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
     * Loads a PHP file and gets its' contents as an array.
     *
     * @param string      $filename
     * @param string|null $group
     *
     * @throws \Viserio\Contracts\Filesystem\Exception\LoadingException
     *
     * @return array|string|null
     */
    public function load($filename, $group = null)
    {
        $data = $this->files->getRequire($filename);

        if ($group !== null) {
            return $this->isGroup($group, (array) $data);
        } else {
            return $data;
        }

        throw new LoadingException('Unable to load config ' . $filename);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($filename)
    {
        return (bool) preg_match('#\.php(\.dist)?$#', $filename);
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
