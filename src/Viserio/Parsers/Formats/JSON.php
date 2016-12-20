<?php
declare(strict_types=1);
namespace Viserio\Parsers\Formats;

use Viserio\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Contracts\Parsers\Exception\DumpException;
use Viserio\Contracts\Parsers\Exception\ParseException;
use Viserio\Contracts\Parsers\Format as FormatContract;

class JSON implements FormatContract, DumperContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        $json = json_decode(trim($payload), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ParseException(
                ['message' => $this->getJsonError(json_last_error())]
            );
        }

        return $json;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        // Clear json_last_error()
        json_encode(null);

        $json = json_encode($data, JSON_PRETTY_PRINT);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // @codeCoverageIgnoreStart
            $jsonError = $this->getJsonError(json_last_error());

            throw new DumpException('JSON dumping failed: ' . $jsonError);
            // @codeCoverageIgnoreEnd
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
    private function getJsonError(int $code): string
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
