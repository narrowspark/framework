<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Formats;

use Throwable;
use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Component\Contracts\Parsers\Exception\ParseException;
use Viserio\Component\Contracts\Parsers\Format as FormatContract;

class PHP implements FormatContract, DumperContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        if (! file_exists($payload)) {
            throw new ParseException(['message' => sprintf('No such file [%s] found.', $payload)]);
        }

        try {
            $data = require $payload;
        } catch (Throwable $exception) {
            throw new ParseException(
                [
                    'message'   => sprintf('An exception was thrown by file [%s].', $payload),
                    'exception' => $exception,
                ]
            );
        }

        return (array) $data;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        $data = var_export($data, true);

        $formatted = str_replace(
            ['  ', '['],
            ['', '['],
            $data
        );

        $output = '<?php
declare(strict_types=1); return ' . $formatted . ';';

        return $output;
    }
}
