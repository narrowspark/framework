<?php
namespace Viserio\Parsers\Formats;

use Exception;
use Sepia\FileHandler;
use Sepia\PoParser;
use Viserio\Contracts\Parsers\Exception\ParseException;
use Viserio\Contracts\Parsers\Format as FormatContract;
use Viserio\Filesystem\Filesystem;

class Po implements FormatContract
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
        if (! class_exists('Sepia\\PoParser')) {
            throw new RuntimeException(
                'Loading translations from the Po format requires the Sepia PoParser component.'
            );
        }

        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        try {
            if ($this->files->exists($filename)) {
                return (new PoParser())->parseFile($filename);
            }
        } catch (Exception $exception) {
            throw new ParseException([
                'message' => 'Unable to parse the Po string'
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
