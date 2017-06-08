<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Parsers;

use Viserio\Component\Contracts\Parsers\Exception\ParseException;
use Viserio\Component\Contracts\Parsers\Parser as ParserContract;

class IniParser implements ParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        $ini = @parse_ini_string($payload, true, INI_SCANNER_RAW);

        if (! $ini) {
            $errors = error_get_last();

            if ($errors === null) {
                $errors['message'] = 'Invalid INI provided.';
            }

            throw new ParseException($errors);
        }

        foreach ($ini as $key => $value) {
            $ini[$key] = self::normalize($value);
        }

        return $ini;
    }
}
