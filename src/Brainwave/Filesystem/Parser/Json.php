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
 * Json.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class Json implements ParserContract
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
     * Loads a JSON file and gets its' contents as an array.
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
        $data = $this->parseJson($filename);

        if (JSON_ERROR_NONE !== json_last_error()) {
            $jsonError = $this->getJsonError(json_last_error());
            throw new \RuntimeException(
                sprintf('Invalid JSON provided "%s" in "%s"', $jsonError, $filename)
            );
        }

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
        return (bool) preg_match('#\.json(\.dist)?$#', $filename);
    }

    /**
     * Parse the json file.
     *
     * @param string $filename
     *
     * @return string|null
     */
    private function parseJson($filename)
    {
        $json = $this->files->get($filename);

        return json_decode($json, true);
    }

    /**
     * Reporting all json erros.
     *
     * @param int $code all json errors
     *
     * @return string
     */
    private function getJsonError($code)
    {
        $errorMessages = [
            JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
            JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX => 'Syntax error',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
        ];

        return isset($errorMessages[$code]) ? $errorMessages[$code] : 'Unknown';
    }

    /**
     * Format a json file for saving.
     *
     * @param array $data data
     *
     * @return string data export
     */
    public function format(array $data)
    {
        return json_encode($data);
    }
}
