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
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\HttpFactory\ResponseFactory;

/**
 * @internal
 *
 * @small
 */
final class HtmlDisplayerTest extends MockeryTestCase
{
    /** @var \Viserio\Component\Exception\Displayer\HtmlDisplayer */
    private $displayer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->displayer = new HtmlDisplayer(
            new ResponseFactory(),
            [
                'viserio' => [
                    'exception' => [
                        'http' => [
                            'html' => [
                                'template_path' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'error.html',
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    public function testServerError(): void
    {
        $response = $this->displayer->display(new Exception(), 'foo', 502, []);
        $expected = \file_get_contents(\dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'error.html');
        $infos = [
            'code' => '502',
            'name' => 'Bad Gateway',
            'detail' => 'The server was acting as a gateway or proxy and received an invalid response from the upstream server.',
            'id' => 'foo',
        ];

        foreach ($infos as $key => $val) {
            $expected = \str_replace('{{ $' . $key . ' }}', $val, $expected);
        }

        self::assertSame($expected, (string) $response->getBody());
        self::assertSame(502, $response->getStatusCode());
        self::assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testClientError(): void
    {
        $response = $this->displayer->display(new Exception(), 'bar', 404, []);
        $expected = \file_get_contents(\dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'error.html');
        $infos = [
            'code' => '404',
            'name' => 'Not Found',
            'detail' => 'The requested resource could not be found but may be available again in the future.',
            'id' => 'bar',
        ];

        foreach ($infos as $key => $val) {
            $expected = \str_replace('{{ $' . $key . ' }}', $val, $expected);
        }

        self::assertSame($expected, (string) $response->getBody());
        self::assertSame(404, $response->getStatusCode());
        self::assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testProperties(): void
    {
        $exception = new Exception();

        self::assertFalse($this->displayer->isVerbose());
        self::assertTrue($this->displayer->canDisplay($exception, $exception, 500));
        self::assertSame('text/html', $this->displayer->getContentType());
    }
}
