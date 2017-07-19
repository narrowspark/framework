<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory;

use Interop\Http\Factory\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Viserio\Component\Http\UploadedFile;

final class UploadedFileFactory implements UploadedFileFactoryInterface
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
    ): UploadedFileInterface {
        return new UploadedFile(
            $file,
            $this->getSize($file, $size),
            $error,
            $clientFilename,
            $clientMediaType
        );
    }

    /**
     * Detect the Uploaded file size.
     *
     * @param mixed    $file
     * @param null|int $size
     *
     * @return int
     */
    private function getSize($file, $size): int
    {
        if (null !== $size) {
            return $size;
        }

        if (\is_string($file)) {
            return \filesize($file);
        }

        return \fstat($file)['size'];
    }
}
