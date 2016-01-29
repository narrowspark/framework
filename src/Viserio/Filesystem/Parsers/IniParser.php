<?php
namespace Viserio\Filesystem\Parsers;

use League\Flysystem\FileNotFoundException;
use Viserio\Contracts\Filesystem\Exception\LoadingException;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Contracts\Filesystem\Parser as ParserContract;
use Viserio\Filesystem\Parsers\Traits\IsGroupTrait;

class IniParser implements ParserContract
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
        if ($this->files->has($filename)) {
            $data = parse_ini_file($filename, true);

            if ($group !== null) {
                return $this->isGroup($group, (array) $data);
            }

            return $data;
        }

        throw new FileNotFoundException($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($filename)
    {
        return (bool) preg_match('/(\.ini)(\.dist)?/', $filename);
    }

    /**
     * Format a file for saving.
     *
     * @param array $data data
     *
     * @return false|string|void
     */
    public function dump(array $data)
    {
        $this->iniFormat($data);
    }

    /**
     * {@inheritdoc}
     */
    private function iniFormat(array $data, array $parent = [])
    {
        $out = '';

        foreach ($data as $k => $v) {
            if (is_array($v)) {
                //subsection case
                //merge all the sections into one array...
                $sec = array_merge($parent, $k);
                //add section information to the output
                $out .= '[' . implode('.', $sec) . ']' . PHP_EOL;
                //recursively traverse deeper
                $out .= $this->iniFormat($v, $sec);
            } else {
                //plain key->value case
                $out .= sprintf('%s=%s', $k, $v) . PHP_EOL;
            }
        }

        return $out;
    }
}
