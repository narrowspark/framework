<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests\Transport;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Swift_Message;
use Swift_Mime_Message;
use Viserio\Component\Mail\Transport\Log as LogTransport;

class LogTransportTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testSend()
    {
        $message = new Swift_Message('Foo subject', 'Bar body');
        $message->setSender('myself@example.com');
        $message->setTo('me@example.com');
        $message->setBcc('you@example.com');

        $logger = $this->mock(LoggerInterface::class);
        $logger->shouldReceive('debug')
            ->once()
            ->with($this->getMimeEntityString($message));

        $transport = new LogTransport($logger);
        $transport->send($message);
    }

    /**
     * Get a loggable string out of a Swiftmailer entity.
     *
     * @param \Swift_Mime_Message $entity
     *
     * @return string
     */
    protected function getMimeEntityString(Swift_Mime_Message $entity): string
    {
        $string = (string) $entity->getHeaders() . PHP_EOL . $entity->getBody();

        foreach ($entity->getChildren() as $children) {
            $string .= PHP_EOL . PHP_EOL . $this->getMimeEntityString($children);
        }

        return $string;
    }
}
