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

use Http\Psr7Test\UriIntegrationTest as Psr7TestUriIntegrationTest;
use Viserio\Component\Http\Uri;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
