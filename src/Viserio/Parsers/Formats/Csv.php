<?php

declare(strict_types=1);
namespace Viserio\Parsers\Formats;

use RuntimeException;
use SplFileObject;
use Viserio\Contracts\Parsers\{
    Exception\ParseException,
    Format as FormatContract
};


class Csv implements FormatContract
{
    /**
     * Sets the delimiter, enclosure, and escape character for CSV.
     *
     * @var array
     */
    private $option = [
        'delimiter' => ';',
        'enclosure' => '"',
        'escape' => '\\',
    ];

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

        $arr = [];
        $from = fopen($payload, 'r+');

        if (! $from) {
            throw new ParseException([
                'message' => 'Failed To Parse Csv',
            ]);
        }

        $headerRowExists = false;

        if ($headerRowExists) {
            // first header row
            $header = fgetcsv($from, 0, $this->option['delimiter'], $this->option['enclosure']);
        }

        while (($data = fgetcsv($from, 0, $this->option['delimiter'], $this->option['enclosure'])) !== false) {
            $arr[] = $headerRowExists ? array_combine($header, $data) : $data;
        }

        fclose($from);

        return $arr;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        $handle = fopen('php://memory', 'rb+');

        foreach ($data as $source => $target) {
            fputcsv(
                $handle,
                [$source, $target],
                $this->option['delimiter'],
                $this->option['enclosure']
            );
        }

        rewind($handle);
        $output = stream_get_contents($handle);
        fclose($handle);

        return $output;
    }
}
