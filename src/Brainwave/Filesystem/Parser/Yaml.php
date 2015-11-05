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
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

/**
 * YamlAdapter.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class Yaml implements ParserContract
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
     * Loads a YAML file and gets its' contents as an array.
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
        if (!class_exists('Symfony\\Component\\Yaml\\Yaml')) {
            throw new \RuntimeException('Unable to read yaml as the Symfony Yaml Component is not installed.');
        }

        try {
            if ($this->files->exists($filename)) {
                $data = (new Parser())->parse($this->files->get($filename));

                if ($group !== null) {
                    return $this->isGroup($group, (array) $data);
                }

                return $data;
            }
        } catch (ParseException $exception) {
            throw new LoadingException(sprintf('Unable to parse the YAML string: [%s]', $exception->getMessage()));
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
        return (bool) preg_match('#\.ya?ml(\.dist)?$#', $filename);
    }

    /**
     * Format a yaml file for saving.
     *
     * @param array $data data
     *
     * @return string data export
     */
    public function format(array $data)
    {
        return YamlParser::dump($data);
    }
}
