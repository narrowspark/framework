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

namespace Viserio\Component\Exception\Tests\Displayer;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\Displayer\SymfonyDisplayer;
use Viserio\Component\HttpFactory\ResponseFactory;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class SymfonyDisplayerTest extends TestCase
{
    /** @var \Viserio\Component\Exception\Displayer\SymfonyDisplayer */
    private $displayer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->displayer = new SymfonyDisplayer(new ResponseFactory());
    }

    public function testServerError(): void
    {
        $response = $this->displayer->display(new Exception(), 'foo', 500, []);

        self::assertSame(500, $response->getStatusCode());
        self::assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testClientError(): void
    {
        $response = $this->displayer->display(new Exception(), 'bar', 401, []);

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testProperties(): void
    {
        self::assertTrue($this->displayer->isVerbose());
        self::assertTrue($this->displayer->canDisplay(new InvalidArgumentException(), new Exception('error', 500), 500));
        self::assertSame('text/html', $this->displayer->getContentType());
    }
}
