<?php
namespace Viserio\Http;

use Psr\Http\Message\UploadedFileInterface;
use Viserio\Contracts\Http\UploadedFileFactory as UploadedFileFactoryContract;

final class UploadedFileFactory implements UploadedFileFactoryContract
{
    /**
     * {@inheritdoc}
     */
    public function createUploadedFile(
        $file,
        int $size = null,
        int $error = \UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ): UploadedFileInterface {
        return new UploadedFile($file, $size, $error, $clientFilename, $clientMediaType);
    }
}
