<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\Tests\DataCollectors\Bridge;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swift_Mailer;
use Swift_SmtpTransport;
use Viserio\Component\WebProfiler\DataCollectors\Bridge\SwiftMailDataCollector;

class SwiftMailDataCollectorTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testGetMenu()
    {
        $collector = $this->getSwiftDataCollector();

        static::assertSame(
            [
                'icon'  => 'ic_mail_outline_white_24px.svg',
                'label' => 'Mails',
                'value' => 0,
            ],
            $collector->getMenu()
        );
    }

    public function testGetPanel()
    {
        $collector = $this->getSwiftDataCollector();

        static::assertSame(
            '<div class="empty">Empty</div>',
            $collector->getPanel()
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
