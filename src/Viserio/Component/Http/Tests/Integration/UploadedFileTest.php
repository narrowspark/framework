<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Integration;

use Http\Psr7Test\UploadedFileIntegrationTest;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Tests\Integration\Traits\BuildTrait;
use Viserio\Component\Http\UploadedFile;

/**
 * @internal
 */
final class UploadedFileTest extends UploadedFileIntegrationTest
{
    use BuildTrait;

    /**
     * {@inheritdoc}
     */
    public function createSubject()
    {
        $stream = new Stream('php://memory', ['mode' => 'rw']);
        $stream->write('foobar');

        return new UploadedFile($stream, $stream->getSize(), \UPLOAD_ERR_OK);
    }
}
