<?php
namespace Viserio\Parsers\Formats;

use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Contracts\Parsers\Exception\DumpException;
use Viserio\Contracts\Parsers\Exception\ParseException;
use Viserio\Contracts\Parsers\Format as FormatContract;

class PHP implements FormatContract
{
    /**
     * {@inheritdoc}
     */
    public function parse($payload)
    {
        if (!file_exists($payload)) {
            throw new ParseException([
                'message' => 'File does not exist.',
            ]);
        }

        return (array) require_once $payload;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data)
    {
        $data = var_export($data, true);

        $formatted = str_replace(
            ['  ', '['],
            ['', '['],
            $data
        );

        $output = '<?php return ' . $formatted . ';';

        return $output;
    }
}
