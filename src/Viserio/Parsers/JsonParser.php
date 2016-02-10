<?php
namespace Viserio\Parsers;

use League\Flysystem\FileNotFoundException;
use Viserio\Contracts\Filesystem\Exception\DumpException;
use Viserio\Contracts\Filesystem\Exception\LoadingException;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Contracts\Filesystem\Parser as ParserContract;

class JsonParser implements ParserContract
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
            $data = $this->parseJson($filename);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $jsonError = $this->getJsonError(json_last_error());

                throw new LoadingException(
                    sprintf('Invalid JSON provided "%s" in "%s"', $jsonError, $filename)
                );
            }

            return (array) $data;
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
     * {@inheritdoc}
     */
    public function dump(array $data)
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);

        if ($json === false) {
            $jsonError = $this->getJsonError(json_last_error());

            throw new DumpException('JSON dumping failed: ' . $jsonError);
        }

        $json = preg_replace('/\[\s+\]/', '[]', $json);
        $json = preg_replace('/\{\s+\}/', '{}', $json);

        return $json;
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
            JSON_ERROR_DEPTH          => 'The maximum stack depth has been exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
            JSON_ERROR_CTRL_CHAR      => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX         => 'Syntax error',
            JSON_ERROR_UTF8           => 'Malformed UTF-8 characters, possibly incorrectly encoded',
        ];

        return isset($errorMessages[$code]) ? $errorMessages[$code] : 'Unknown error';
    }
}
