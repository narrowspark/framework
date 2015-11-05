<?php
namespace Brainwave\Mail\Test;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Brainwave\Mail\Transport\Mandrill;

/**
 * MailMandrillTransportTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class MailMandrillTransportTest extends \PHPUnit_Framework_TestCase
{
    public function testSend()
    {
        $message = new \Swift_Message('Foo subject', 'Bar body');
        $message->setTo('me@example.com');
        $message->setBcc('you@example.com');

        $transport = new MandrillTransportStub('testkey');
        $client    = $this->getMock('GuzzleHttp\Client', ['post']);
        $transport->setHttpClient($client);

        $client->expects($this->once())
            ->method('post')
            ->with($this->equalTo('https://mandrillapp.com/api/1.0/messages/send-raw.json'),
                $this->equalTo([
                    'body' => [
                        'key'         => 'testkey',
                        'raw_message' => $message->toString(),
                        'async'       => false,
                        'to'          => ['me@example.com', 'you@example.com']
                    ]
                ])
            );

        $transport->send($message);
    }
}

class MandrillTransportStub extends Mandrill
{
    protected $client;

    protected function getHttpClient()
    {
        return $this->client;
    }

    public function setHttpClient($client)
    {
        $this->client = $client;
    }
}
