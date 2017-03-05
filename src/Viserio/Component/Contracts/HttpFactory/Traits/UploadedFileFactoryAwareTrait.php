<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\HttpFactory\Traits;

use Interop\Http\Factory\UploadedFileFactoryInterface;
use RuntimeException;

trait UploadedFileFactoryAwareTrait
{
    /**
     * A UploadedFileFactory instance.
     *
     * @var \Interop\Http\Factory\UploadedFileFactoryInterface
     */
    protected $uploaded;

    /**
     * Set a UploadedFileFactory instance.
     *
     * @param \Interop\Http\Factory\UploadedFileFactoryInterface $uploaded
     *
     * @return $this
     */
    public function setUploadedFileFactory(UploadedFileFactoryInterface $uploaded)
    {
        $this->uploaded = $uploaded;

        return $this;
    }

    /**
     * Get the UploadedFileFactory instance.
     *
     * @throws \RuntimeException
     *
     * @return \Interop\Http\Factory\UploadedFileFactoryInterface
     */
    public function getUploadedFileFactory(): UploadedFileFactoryInterface
    {
        if (! $this->uploaded) {
            throw new RuntimeException('Instance implementing [\Interop\Http\Factory\UploadedFileFactoryInterface] is not set up.');
        }

        return $this->uploaded;
    }
}
