<?php
namespace Viserio\Parsers\Formats;

use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Contracts\Parser\Exception\DumpException;
use Viserio\Contracts\Parser\Exception\ParseException;
use Viserio\Contracts\Parsers\Format as FormatContract;

class JSON implements FormatContract
{
    /**
     * {@inheritdoc}
     */
    public function parse($payload)
    {
        $json = json_decode(trim($payload), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $jsonError = $this->getJsonError(json_last_error());

            throw new ParseException(
                sprintf('Invalid JSON provided "%s" in "%s"', $jsonError)
            );
        }

        return $json;
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
