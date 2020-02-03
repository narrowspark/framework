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

namespace Viserio\Component\Http\Tests;

use Http\Psr7Test\UploadedFileIntegrationTest as Psr7TestUploadedFileIntegrationTest;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\UploadedFile;

/**
 * @internal
 *
 * @small
 */
final class UploadedFileIntegrationTest extends Psr7TestUploadedFileIntegrationTest
{
    /**
     * {@inheritdoc}
     */
    public function createSubject()
    {
        $stream = new Stream('php://memory', ['mode' => 'rw']);
        $stream->write('foobar');

        return new UploadedFile($stream, $stream->getSize(), \UPLOAD_ERR_OK);
    }
}
