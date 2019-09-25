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
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\HttpFactory\ResponseFactory;

/**
 * @internal
 *
 * @small
 */
final class JsonDisplayerTest extends TestCase
{
    /** @var \Viserio\Component\Exception\Displayer\JsonDisplayer */
    private $displayer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->displayer = new JsonDisplayer(new ResponseFactory());
    }

    public function testServerError(): void
    {
        $response = $this->displayer->display(new Exception(), 'foo', 500, []);
        $expected = '{"errors":[{"id":"foo","status":500,"title":"Internal Server Error","detail":"An error has occurred and this resource cannot be displayed."}]}';

        self::assertSame(\trim($expected), (string) $response->getBody());
        self::assertSame(500, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testClientError(): void
    {
        $response = $this->displayer->display(new Exception(), 'bar', 401, []);
        $expected = '{"errors":[{"id":"bar","status":401,"title":"Unauthorized","detail":"Authentication is required and has failed or has not yet been provided."}]}';

        self::assertSame(\trim($expected), (string) $response->getBody());
        self::assertSame(401, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testProperties(): void
    {
        self::assertFalse($this->displayer->isVerbose());
        self::assertTrue($this->displayer->canDisplay(new InvalidArgumentException(), new Exception('error', 500), 500));
        self::assertSame('application/json', $this->displayer->getContentType());
    }
}
