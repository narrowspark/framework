<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
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
     * @return static
     */
    public function setUploadedFileFactory(UploadedFileFactoryInterface $uploadedFileFactory): self
    {
        $this->uploadedFileFactory = $uploadedFileFactory;

        return $this;
    }
}
