<?php
namespace Viserio\Mail\Test;

use Aws\Ses\SesClient;
use Viserio\Application\Application;
use Viserio\Mail\TransportManager;
use Viserio\Mail\Transport\Ses as SesTransport;
use Viserio\Support\Collection;

class MailSesTransportTest extends \PHPUnit_Framework_TestCase
{
    // public function testGetTransport()
    // {
    //     /** @var Application $app */
    //     $app = [
    //         'config' => new Collection([
    //             'services.ses' => [
    //                 'key'    => 'foo',
    //                 'secret' => 'bar',
    //                 'region' => 'us-east-1',
    //             ]
    //         ])
    //     ];

    //     $manager = new TransportManager($app);

    //     /** @var SesTransport $transport */
    //     $transport = $manager->driver('ses');

    //     /** @var SesClient $ses */
    //     $ses = $this->readAttribute($transport, 'ses');
    //     $this->assertEquals('us-east-1', $ses->getRegion());
    // }

    public function testSend()
    {
        $message = new \Swift_Message('Foo subject', 'Bar body');
        $message->setSender('myself@example.com');
        $message->setTo('me@example.com');
        $message->setBcc('you@example.com');

        $client = $this->getMockBuilder('Aws\Ses\SesClient')
            ->setMethods(['sendRawEmail'])
            ->disableOriginalConstructor()
            ->createMock();
        $transport = new SesTransport($client);

        $client->expects($this->once())
            ->method('sendRawEmail')
            ->with($this->equalTo([
                'Source' => 'myself@example.com',
                'Destinations' => [
                    'me@example.com',
                    'you@example.com',
                ],
                'RawMessage' => ['Data' => (string) $message],
            ])
        );

        $transport->send($message);
    }
}
