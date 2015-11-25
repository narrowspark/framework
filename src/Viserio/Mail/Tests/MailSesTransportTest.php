<?php
namespace Viserio\Mail\Test;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.10.0
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Aws\Ses\SesClient;
use Viserio\Application\Application;
use Viserio\Mail\Transport\Ses as SesTransport;
use Viserio\Mail\TransportManager;
use Viserio\Support\Collection;

/**
 * MailSesTransportTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0
 */
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
            ->getMock();
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
