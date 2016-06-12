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
        $data,
        int $size,
        int $error,
        string $clientFile = '',
        string $clientMediaType = ''
    ): UploadedFileInterface {
        return new UploadedFile($data, $size, $error, $clientFile, $clientMediaType);
    }
}
