<?php
namespace Viserio\Filesystem\Parsers;

use League\Flysystem\FileNotFoundException;
use Viserio\Contracts\Filesystem\Exception\LoadingException;
use Viserio\Contracts\Filesystem\Parser as ParserContract;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Filesystem\Parsers\Traits\IsGroupTrait;

class JsonParser implements ParserContract
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
            $data = $this->parseJson($filename);

            if (JSON_ERROR_NONE !== json_last_error()) {
                $jsonError = $this->getJsonError(json_last_error());
                throw new LoadingException(
                    sprintf('Invalid JSON provided "%s" in "%s"', $jsonError, $filename)
                );
            }

            if ($group !== null) {
                return $this->isGroup($group, (array) $data);
            } else {
                return (array) $data;
            }
        }

        throw new FileNotFoundException($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($filename)
    {
        return (bool) preg_match('/(\.json)(\.dist)?/', $filename);
    }

    /**
     * Format a json file for saving.
     *
     * @param array $data data
     *
     * @return string data export
     */
    public function dump(array $data)
    {
        return json_encode($data);
    }

    /**
     * {@inheritdoc}
     */
    private function parseJson($filename)
    {
        $json = $this->files->read($filename);

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
}
