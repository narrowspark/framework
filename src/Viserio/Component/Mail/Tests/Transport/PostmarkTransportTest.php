<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests\Transport;

use GuzzleHttp\Client as HttpClient;
use PHPUnit\Framework\TestCase;
use Swift_Attachment;
use Swift_Message;
use Viserio\Component\Mail\Transport\PostmarkTransport;

/**
 * @internal
 */
final class PostmarkTransportTest extends TestCase
{
    public function testSend(): void
    {
        $message = new Swift_Message('Is alive!', 'Spark');
        $message->setFrom('me@example.com', 'Me #5');
        $message->addTo('you@example.com', 'A. Friend');
        $message->addTo('you+two@example.com');
        $message->addCc('another+1@example.com');
        $message->addCc('another+2@example.com', 'Extra 2');
        $message->addBcc('another+3@example.com');
        $message->addBcc('another+4@example.com', 'Extra 4');
        $message->addPart('<q>Narrowspark</q>', 'text/html');

        $attachment = new Swift_Attachment('This is the plain text attachment.', 'hello.txt', 'text/plain');

        $attachment2 = new Swift_Attachment('This is the plain text attachment.', 'hello.txt', 'text/plain');
        $attachment2->setDisposition('inline');

        $message->attach($attachment);
        $message->attach($attachment2);
        $message->setPriority(1);

        $headers = $message->getHeaders();

        $client = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['post'])
            ->getMock();

        $version = \PHP_VERSION ?? 'Unknown PHP version';
        $os      = \PHP_OS      ?? 'Unknown OS';

        $client->expects($this->once())
            ->method('post')
            ->with(
                $this->equalTo('https://api.postmarkapp.com/email'),
                $this->equalTo([
                    'headers' => [
                        'X-Postmark-Server-Token' => 'TESTING_SERVER',
                        'User-Agent'              => "postmark (PHP Version: ${version}, OS: ${os})",
                        'Content-Type'            => 'application/json',
                    ],
                    'json' => [
                        'From'     => '"Me #5" <me@example.com>',
                        'To'       => '"A. Friend" <you@example.com>,you+two@example.com',
                        'Cc'       => 'another+1@example.com,"Extra 2" <another+2@example.com>',
                        'Bcc'      => 'another+3@example.com,"Extra 4" <another+4@example.com>',
                        'Subject'  => 'Is alive!',
                        'HtmlBody' => '<q>Narrowspark</q>',
                        'Headers'  => [
                            ['Name' => 'Message-ID', 'Value' => '<' . $headers->get('Message-ID')->getId() . '>'],
                            ['Name' => 'X-PM-KeepID', 'Value' => 'true'],
                            ['Name' => 'X-Priority', 'Value' => '1 (Highest)'],
                        ],
                        'Attachments' => [
                            [
                                'ContentType' => 'text/plain',
                                'Content'     => 'VGhpcyBpcyB0aGUgcGxhaW4gdGV4dCBhdHRhY2htZW50Lg==',
                                'Name'        => 'hello.txt',
                            ],
                            [
                                'ContentType' => 'text/plain',
                                'Content'     => 'VGhpcyBpcyB0aGUgcGxhaW4gdGV4dCBhdHRhY2htZW50Lg==',
                                'Name'        => 'hello.txt',
                                'ContentID'   => 'cid:' . $attachment2->getId(),
                            ],
                        ],
                        'Tag' => '',
                    ],
                ])
           );

        $transport = new PostmarkTransport($client, 'TESTING_SERVER');

        $transport->send($message);
    }

    public function testSetAndGetServerToken(): void
    {
        $client = $this->getMockBuilder(HttpClient::class)
            ->getMock();

        $transport = new PostmarkTransport($client, 'TESTING_SERVER');

        $transport->setServerToken('token');

        $this->assertSame('token', $transport->getServerToken());
    }
}
