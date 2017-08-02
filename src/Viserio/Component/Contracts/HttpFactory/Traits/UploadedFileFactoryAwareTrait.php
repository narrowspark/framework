<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\HttpFactory\Traits;

use Interop\Http\Factory\UploadedFileFactoryInterface;

trait UploadedFileFactoryAwareTrait
{
    /**
     * A UploadedFileFactory instance.
     *
     * @var \Interop\Http\Factory\UploadedFileFactoryInterface
     */
    protected $uploadedFileFactory;

    /**
     * Set a UploadedFileFactory instance.
     *
     * @param \Interop\Http\Factory\UploadedFileFactoryInterface $uploadedFileFactory
     *
     * @return $this
     */
    public function setUploadedFileFactory(UploadedFileFactoryInterface $uploadedFileFactory)
    {
        $this->uploadedFileFactory = $uploadedFileFactory;

        return $this;
    }
}
