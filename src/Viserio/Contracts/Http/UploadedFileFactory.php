<?php
namespace Viserio\Contracts\Http;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

interface UploadedFileFactory
{
    /**
     * Create a PSR-7 Uploaded Object.
     *
     * @param StreamInterface|string|resource $data
     * @param int                             $size
     * @param int                             $error
     * @param string                          $clientFile
     * @param string                          $clientMediaType
     *
     * @return UploadedFileInterface
     */
    public function createUploadedFile(
        $data,
        int $size,
        int $error,
        string $clientFile = '',
        string $clientMediaType = ''
    ): UploadedFileInterface;
}
