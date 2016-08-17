<?php
declare(strict_types=1);
namespace Viserio\Mail\Tests\Transport;

use GuzzleHttp\Client as HttpClient;
use Swift_Message;
use Viserio\Mail\Tests\Fixture\MandrillTransportStub;

class MandrillTransportTest extends \PHPUnit_Framework_TestCase
{
    public function testSend()
    {
        $message = new Swift_Message('Foo subject', 'Bar body');
        $message->setTo('me@example.com');
        $message->setBcc('you@example.com');

        $client = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['post'])
            ->getMock();

        $transport = new MandrillTransportStub($client, 'testkey');

        $client->expects($this->once())
            ->method('post')
            ->with(
                $this->equalTo('https://mandrillapp.com/api/1.0/messages/send-raw.json'),
                $this->equalTo([
                    'form_params' => [
                        'key' => 'testkey',
                        'raw_message' => $message->toString(),
                        'async' => false,
                        'to' => ['me@example.com', 'you@example.com'],
                    ],
                ])
            );

        $transport->send($message);
    }
}
