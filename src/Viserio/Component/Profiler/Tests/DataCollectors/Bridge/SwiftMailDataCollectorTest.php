<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\DataCollectors\Bridge;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swift_Mailer;
use Swift_SmtpTransport;
use Viserio\Component\Profiler\DataCollectors\Bridge\SwiftMailDataCollector;

class SwiftMailDataCollectorTest extends MockeryTestCase
{
    public function testGetMenu()
    {
        $collector = $this->getSwiftDataCollector();

        self::assertSame(
            [
                'icon'  => 'ic_mail_outline_white_24px.svg',
                'label' => 'Mails',
                'value' => 0,
            ],
            $collector->getMenu()
        );
    }

    private function getSwiftDataCollector()
    {
        $collector = new SwiftMailDataCollector(
            Swift_Mailer::newInstance(Swift_SmtpTransport::newInstance('smtp.example.org', 25))
        );

        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        return $collector;
    }
}
