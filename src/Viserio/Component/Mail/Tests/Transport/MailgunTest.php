<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests\Transport;

use GuzzleHttp\Client as HttpClient;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Swift_Message;
use Viserio\Component\Mail\Transport\MailgunTransport;

/**
 * @internal
 */
final class MailgunTest extends MockeryTestCase
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $httpMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->httpMock = $this->mock(HttpClient::class);
    }

    public function testSetAndGetDomain(): void
    {
        $transport = new MailgunTransport($this->httpMock, 'API_KEY', 'narrowspark');
        $transport->setDomain('anolilab.com');

        $this->assertSame('anolilab.com', $transport->getDomain());
    }

    public function testSetAndGetKey(): void
    {
        $transport = new MailgunTransport($this->httpMock, 'API_KEY', 'narrowspark');
        $transport->setKey('test');

        $this->assertSame('test', $transport->getKey());
    }

    public function testSend(): void
    {
        $message = new Swift_Message('Foo subject', 'Bar body');
        $message->setSender('myself@example.com');
        $message->setTo('me@example.com');
        $message->setBcc('you@example.com');

        $this->httpMock->shouldReceive('post')
            ->andReturn(
                'https://api.mailgun.net/v3/narrowspark/messages.mime',
                [
                    'auth' => [
                        'api',
                        'API_KEY',
                    ],
                    'multipart' => [
                        ['name' => 'to', 'contents' => 'me@example.com'],
                        ['name' => 'cc', 'contents' => ''],
                        ['name' => 'bcc', 'contents' => 'you@example.com'],
                        ['name' => 'message', 'contents' => $message->toString(), 'filename' => 'message.mime'],
                    ],
                ]
            );

        $transport = new MailgunTransport($this->httpMock, 'API_KEY', 'narrowspark');

        $message2 = clone $message;
        $message2->setBcc([]);

        $transport->send($message);
    }

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods(bool $allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }
}
