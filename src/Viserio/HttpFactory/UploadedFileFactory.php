<?php
declare(strict_types=1);
namespace Viserio\HttpFactory;

use Viserio\Contracts\HttpFactory\UploadedFileFactory as UploadedFileFactoryContract;
use Viserio\Http\UploadedFile;

final class UploadedFileFactory implements UploadedFileFactoryContract
{
    /**
     * {@inheritdoc}
     */
    public function createUploadedFile(
        $file,
        $size = null,
        $error = \UPLOAD_ERR_OK,
        $clientFilename = null,
        $clientMediaType = null
    ) {
        return new UploadedFile(
            $file,
            $this->getSize($file, $size),
            $error,
            $clientFilename,
            $clientMediaType
        );
    }

    /**
     * Detect the Uploaded file size
     *
     * @param mixed    $file
     * @param int|null $size
     *
     * @return int
     */
    protected function getSize($file, $size): int
    {
        if (null !== $size) {
            return $size;
        }

        if (is_string($file)) {
            return filesize($file);
        }

        return fstat($file)['size'];
    }
}
