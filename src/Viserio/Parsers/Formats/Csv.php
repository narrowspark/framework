<?php
namespace Viserio\Parsers\Formats;

use RuntimeException;
use SplFileObject;
use Viserio\Contracts\Filesystem\ParseException;
use Viserio\Contracts\Parsers\Format as FormatContract;
use Viserio\Filesystem\Filesystem;

class Csv implements FormatContract
{
    /**
     * The filesystem instance.
     *
     * @var \Viserio\Filesystem\Filesystem
     */
    protected $files;

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
    public function parse(string $payload): array
    {
        try {
            if ($this->files->exists($payload)) {
               $file = new SplFileObject($payload, 'rb');
            }
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
