<?php
declare(strict_types=1);
namespace Viserio\Parsers\Formats;

use Viserio\Contracts\Parsers\Exception\ParseException;
use Viserio\Contracts\Parsers\Format as FormatContract;

class PHP implements FormatContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        if (! file_exists($payload)) {
            throw new ParseException([
                'message' => 'File not found.',
            ]);
        }

        return (array) require_once $payload;
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
