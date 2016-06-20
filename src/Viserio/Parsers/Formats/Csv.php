<?php
namespace Viserio\Parsers\Formats;

use Exception;
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
            if ($this->files->exists($filename)) {
                return '';
            }
        } catch (Exception $exception) {
            throw new ParseException([
                'message' => 'Failed To Parse Csv',
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        //
    }
}
