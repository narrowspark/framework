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

namespace Viserio\Component\Http\Tests;

use Http\Psr7Test\UploadedFileIntegrationTest as Psr7TestUploadedFileIntegrationTest;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\UploadedFile;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
