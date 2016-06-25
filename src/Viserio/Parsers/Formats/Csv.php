<?php
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

        try {
            $file = new SplFileObject($payload, 'rb');
        } catch (RuntimeException $exception) {
            throw new ParseException([
                'message' => 'Failed To Parse Csv',
            ]);
        }

        $array = [];

        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
        $file->setCsvControl($this->option['delimiter'], $this->option['enclosure'], $this->option['escape']);

        foreach ($file as $data) {
            if ('#' !== substr($data[0], 0, 1) && isset($data[1]) && 2 === count($data)) {
                $array[$data[0]] = $data[1];
            }
        }

        return $array;
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
