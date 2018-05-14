<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\HttpFactory\Traits;

use Psr\Http\Message\UploadedFileFactoryInterface;

trait UploadedFileFactoryAwareTrait
{
    /**
     * A UploadedFileFactory instance.
     *
     * @var null|\Psr\Http\Message\UploadedFileFactoryInterface
     */
    protected $uploadedFileFactory;

    /**
     * Set a UploadedFileFactory instance.
     *
     * @param \Psr\Http\Message\UploadedFileFactoryInterface $uploadedFileFactory
     *
     * @return static
     */
    public function setUploadedFileFactory(UploadedFileFactoryInterface $uploadedFileFactory): self
    {
        $this->uploadedFileFactory = $uploadedFileFactory;

        return $this;
    }
}
