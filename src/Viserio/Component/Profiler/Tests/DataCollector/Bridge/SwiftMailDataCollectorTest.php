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

namespace Viserio\Component\Profiler\Tests\DataCollector\Bridge;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swift_Mailer;
use Swift_SmtpTransport;
use Viserio\Component\Profiler\DataCollector\Bridge\SwiftMailDataCollector;

/**
 * @internal
 *
 * @small
 */
final class SwiftMailDataCollectorTest extends MockeryTestCase
{
    public function testGetMenu(): void
    {
        $collector = $this->getSwiftDataCollector();

        self::assertSame(
            [
                'icon' => 'ic_mail_outline_white_24px.svg',
                'label' => 'Mails',
                'value' => 0,
            ],
            $collector->getMenu()
        );
    }

    private function getSwiftDataCollector()
    {
        $collector = new SwiftMailDataCollector(
            new Swift_Mailer(new Swift_SmtpTransport('smtp.example.org', 25))
        );

        $collector->collect(
            Mockery::mock(ServerRequestInterface::class),
            Mockery::mock(ResponseInterface::class)
        );

        return $collector;
    }
}
