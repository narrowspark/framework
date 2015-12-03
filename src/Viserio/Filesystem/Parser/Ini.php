<?php
namespace Viserio\Filesystem\Parser;

use Viserio\Contracts\Filesystem\LoadingException;
use Viserio\Contracts\Filesystem\Parser as ParserContract;
use Viserio\Filesystem\Filesystem;
use Viserio\Filesystem\Parser\Traits\IsGroupTrait;

/**
 * Ini.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
class Ini implements ParserContract
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
     * Loads a INI file and gets its' contents as an array.
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
        if ($this->files->exists($filename)) {
            $data = parse_ini_file($filename, true);

            if ($group !== null) {
                return $this->isGroup($group, (array) $data);
            }

            return $data;
        }

        throw new LoadingException('Unable to load config '.$filename);
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
        return (bool) preg_match('#\.ini(\.dist)?$#', $filename);
    }

    /**
     * Format a file for saving.
     *
     * @param array $data data
     *
     * @return false|string|void
     */
    public function format(array $data)
    {
        $this->iniFormat((array) $data);
    }

    /**
     * Format a ini file.
     *
     * @param array $data
     * @param array $parent
     *
     * @return string data export
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
                $out .= '['.implode('.', $sec).']'.PHP_EOL;
                //recursively traverse deeper
                $out .= $this->iniFormat($v, $sec);
            } else {
                //plain key->value case
                $out .= sprintf('%s=%s', $k, $v).PHP_EOL;
            }
        }

        return $out;
    }
}
