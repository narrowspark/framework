<?php
declare(strict_types=1);
namespace Viserio\Contracts\HttpFactory\Traits;

use Interop\Http\Factory\UploadedFactoryInterface;
use RuntimeException;

trait UploadedFileFactoryAwareTrait
{
    /**
     * A UploadedFactory instance.
     *
     * @var \Interop\Http\Factory\UploadedFactoryInterface
     */
    protected $uploaded;

    /**
     * Set a UploadedFactory instance.
     *
     * @param \Interop\Http\Factory\UploadedFactoryInterface $uploaded
     *
     * @return $this
     */
    public function setUploadedFactory(UploadedFactoryInterface $uploaded)
    {
        $this->uploaded = $uploaded;

        return $this;
    }

    /**
     * Get the UploadedFactory instance.
     *
     * @throws \RuntimeException
     *
     * @return \Interop\Http\Factory\UploadedFactoryInterface
     */
    public function getUploadedFactory(): UploadedFactoryInterface
    {
        if (! $this->uploaded) {
            throw new RuntimeException('Instance implementing \Interop\Http\Factory\UploadedFactoryInterface is not set up.');
        }

        return $this->uploaded;
    }
}
