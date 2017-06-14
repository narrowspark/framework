<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\DataCollector\Bridge;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swift_Mailer;
use Swift_SmtpTransport;
use Viserio\Component\Profiler\DataCollector\Bridge\SwiftMailDataCollector;

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
            new Swift_Mailer(new Swift_SmtpTransport('smtp.example.org', 25))
        );

        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        return $collector;
    }
}
