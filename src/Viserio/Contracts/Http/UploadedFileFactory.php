<?php

declare(strict_types=1);
namespace Viserio\Contracts\Http;

use Psr\Http\Message\UploadedFileInterface;

interface UploadedFileFactory
{
    /**
     * Create a new uploaded file.
     *
     * If a string is passed it is assumed to be a file path.
     *
     * If a size is not provided it will be determined by checking the size of
     * the file.
     *
     * @see http://php.net/manual/features.file-upload.post-method.php
     * @see http://php.net/manual/features.file-upload.errors.php
     *
     * @param string|resource $file
     * @param int             $size            in bytes
     * @param int             $error           PHP file upload error
     * @param string          $clientFilename
     * @param string          $clientMediaType
     *
     * @return UploadedFileInterface
     */
    public function createUploadedFile(
        $file,
        int $size = null,
        int $error = \UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ): UploadedFileInterface;
}
