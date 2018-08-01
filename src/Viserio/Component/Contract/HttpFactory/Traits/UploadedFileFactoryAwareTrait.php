<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\HttpFactory\Traits;

use Psr\Http\Message\UploadedFileFactoryInterface;

trait UploadedFileFactoryAwareTrait
{
    /**
     * A UploadedFileFactory instance.
     *
     * @var \Psr\Http\Message\UploadedFileFactoryInterface
     */
    protected $uploadedFileFactory;

    /**
     * Set a UploadedFileFactory instance.
     *
     * @param \Psr\Http\Message\UploadedFileFactoryInterface $uploadedFileFactory
     *
     * @return $this
     */
    public function setUploadedFileFactory(UploadedFileFactoryInterface $uploadedFileFactory)
    {
        $this->uploadedFileFactory = $uploadedFileFactory;

        return $this;
    }
}
