<?php
declare(strict_types=1);
namespace Viserio\Contracts\HttpFactory;

interface UploadedFileFactory
{
    /**
     * Create a new uploaded file.
     *
     * If a string is used to create the file, a temporary resource will be
     * created with the content of the string.
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
     * @throws \InvalidArgumentException
     *                                   If the file resource is not readable.
     *
     * @return \Psr\Http\Message\UploadedFileInterface
     */
    public function createUploadedFile(
        $file,
        $size = null,
        $error = \UPLOAD_ERR_OK,
        $clientFilename = null,
        $clientMediaType = null
    );
}
