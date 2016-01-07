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
        $output = '';

        foreach ($data as $section => $array) {
            $output .= $this->writeSection($section, $array);
        }

        return $output;
    }

    protected function writeSection($section, $array)
    {
        $subsections = [];
        $output = "[$section]\n";

        foreach ($array as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $key = $section . '.' . $key;
                $subsections[$key] = (array) $value;
            } else {
                $output .= str_replace('=', '_', $key) . '=';

                if (is_string($value)) {
                    $output .= '"' . addslashes($value) . '"';
                } elseif (is_bool($value)) {
                    $output .= $value ? 'true' : 'false';
                } else {
                    $output .= $value;
                }

                $output .= "\n";
            }
        }

        if ($subsections) {
            $output .= "\n";

            foreach ($subsections as $section => $array) {
                $output .= $this->writeSection($section, $array);
            }
        }

        return $output;
    }
}
