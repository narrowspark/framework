<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests\Transport;

use Mockery as Mock;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use Swift_Message;
use Swift_Mime_MimeEntity;
use Viserio\Component\Mail\Transport\LogTransport;

class LogTransportTest extends MockeryTestCase
{
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
     * @param \Swift_Mime_MimeEntity $entity
     *
     * @return string
     */
    protected function getMimeEntityString(Swift_Mime_MimeEntity $entity): string
    {
        $string = (string) $entity->getHeaders() . PHP_EOL . $entity->getBody();

        foreach ($entity->getChildren() as $children) {
            $string .= PHP_EOL . PHP_EOL . $this->getMimeEntityString($children);
        }

        return $string;
    }
}
