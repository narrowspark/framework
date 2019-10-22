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

namespace Viserio\Component\Exception\Tests\Displayer;

use Exception;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\Displayer\WhoopsJsonDisplayer;
use Viserio\Component\HttpFactory\ResponseFactory;

/**
 * @internal
 *
 * @small
 */
final class WhoopsJsonDisplayerTest extends TestCase
{
    /** @var \Viserio\Component\Exception\Displayer\WhoopsJsonDisplayer */
    private $whoops;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->whoops = new WhoopsJsonDisplayer(new ResponseFactory());
    }

    public function testServerError(): void
    {
        $response = $this->whoops->display(new Exception(), 'foo', 503, []);

        self::assertIsString((string) $response->getBody());
        self::assertSame(503, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testClientError(): void
    {
        $response = $this->whoops->display(new Exception(), 'bar', 403, []);

        self::assertIsString((string) $response->getBody());
        self::assertSame(403, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testProperties(): void
    {
        $exception = new Exception();
        $displayer = $this->whoops;

        self::assertTrue($displayer->isVerbose());
        self::assertTrue($displayer->canDisplay($exception, $exception, 500));
        self::assertSame('application/json', $displayer->getContentType());
    }
}
