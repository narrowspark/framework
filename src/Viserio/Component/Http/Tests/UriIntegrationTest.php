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

use Http\Psr7Test\UriIntegrationTest as Psr7TestUriIntegrationTest;
use Viserio\Component\Http\Uri;

/**
 * @internal
 *
 * @small
 */
final class UriIntegrationTest extends Psr7TestUriIntegrationTest
{
    /** @var array with functionName => reason */
    protected $skippedTests = [
        'testWithSchemeInvalidArguments' => 'League\Uri\AbstractUri::filterString only supports string',
    ];

    /**
     * {@inheritdoc}
     */
    public function createUri($uri)
    {
        return Uri::createFromString($uri);
    }
}
