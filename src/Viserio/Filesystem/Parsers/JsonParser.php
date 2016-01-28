<?php
namespace Viserio\Filesystem\Parser;

use Viserio\Contracts\Filesystem\Exception\LoadingException;
use Viserio\Contracts\Filesystem\Parser as ParserContract;
use Viserio\Filesystem\Filesystem;
use Viserio\Filesystem\Parser\Traits\IsGroupTrait;

class Json implements ParserContract
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
     * {@inheritdoc}
     */
    public function parse($filename, $group = null)
    {
        if ($this->files->exists($filename)) {
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
                return $data;
            }
        }

        throw new LoadingException('Unable to load config ' . $filename);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($filename)
    {
        return (bool) preg_match(/(\.json)(\.dist)?/, $filename);
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
