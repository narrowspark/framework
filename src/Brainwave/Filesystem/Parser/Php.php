<?php
namespace Brainwave\Filesystem\Parser;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Brainwave\Contracts\Filesystem\LoadingException;
use Brainwave\Contracts\Filesystem\Parser as ParserContract;
use Brainwave\Filesystem\Filesystem;
use Brainwave\Filesystem\Parser\Traits\IsGroupTrait;

/**
 * Php.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class Php implements ParserContract
{
    use IsGroupTrait;

    /**
     * The filesystem instance.
     *
     * @var \Brainwave\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new file filesystem loader.
     *
     * @param \Brainwave\Filesystem\Filesystem $files
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
     * @throws \Exception
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
        return (bool) preg_match('#\.php(\.dist)?$#', $filename);
    }

    /**
     * Format a php file for saving.
     *
     * @param array $data data
     *
     * @return string data export
     */
    public function format(array $data)
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
