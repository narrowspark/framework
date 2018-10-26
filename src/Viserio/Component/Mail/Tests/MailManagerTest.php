<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Nyholm\NSA;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Swift_DependencyContainer;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Contract\Mail\Mailer as MailerContract;
use Viserio\Component\Contract\View\Factory as ViewFactoryContract;
use Viserio\Component\Mail\Mailer;
use Viserio\Component\Mail\MailManager;
use Viserio\Component\Mail\Transport\MandrillTransport;
use Viserio\Component\Mail\TransportFactory;

/**
 * @internal
 */
final class MailManagerTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Mail\MailManager
     */
    private $mailManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $config = [
            'viserio' => [
                'mail' => [
                    'domain' => 'narrowspark.com',
                    'from'   => [
                        'address' => 'hello@example.com',
                        'name'    => 'Example',
                    ],
                    'to' => [
                        'address' => 'hello@example.com',
                        'name'    => 'Example',
                    ],
                    'reply_to' => [
                        'address' => 'hello@example.com',
                        'name'    => 'Example',
                    ],
                    'connections' => [
                        'smtp' => [
                            'transporter' => [
                                'host'       => '',
                                'port'       => '',
                                'encryption' => '',
                                'username'   => '',
                                'password'   => '',
                                'stream'     => '',
                            ],
                        ],
                        'mailgun' => [
                            'transporter' => [
                                'secret' => '',
                                'domain' => '',
                            ],
                        ],
                        'mandrill' => [
                            'transporter' => [
                                'secret' => '',
                            ],
                        ],
                        'sparkpost' => [
                            'transporter' => [
                                'secret' => '',
                                'domain' => '',
                            ],
                        ],
                        'ses' => [
                            'transporter' => [
                                'secret' => 'test',
                                'key'    => 'test',
                                'region' => 'us-west-2',
                            ],
                        ],
                        'custom' => [
                            'driver'      => 'mandrill',
                            'transporter' => [
                                'secret' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $factory = new TransportFactory();
        $factory->setLogger($this->mock(LoggerInterface::class));

        $this->mailManager = new MailManager($config, $factory);
    }

    public function testGetDefaultConnection(): void
    {
        $this->assertSame('array', $this->mailManager->getDefaultConnection());
    }

    public function testDefaultConnection(): void
    {
        $this->assertInstanceOf(Mailer::class, $this->mailManager->getConnection());
    }

    public function testGetConnectionThrowAException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Support\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Mailer [test] is not supported.');

        $this->mailManager->getConnection('test');
    }

    public function testLogMailer(): void
    {
        $this->assertInstanceOf(Mailer::class, $this->mailManager->getConnection('log'));
    }

    public function testSendmailMailer(): void
    {
        $this->assertInstanceOf(Mailer::class, $this->mailManager->getConnection('sendmail'));
    }

    public function testArrayMailer(): void
    {
        $this->assertInstanceOf(Mailer::class, $this->mailManager->getConnection('array'));
    }

    public function testSmtpMailer(): void
    {
        $this->assertInstanceOf(Mailer::class, $this->mailManager->getConnection('smtp'));
    }

    public function testMailgunMailer(): void
    {
        $this->assertInstanceOf(Mailer::class, $this->mailManager->getConnection('mailgun'));
    }

    public function testMandrillMailer(): void
    {
        $this->assertInstanceOf(Mailer::class, $this->mailManager->getConnection('mandrill'));
    }

    public function testSparkPostMailer(): void
    {
        $this->assertInstanceOf(Mailer::class, $this->mailManager->getConnection('sparkpost'));
    }

    public function testSesMailer(): void
    {
        $this->assertInstanceOf(Mailer::class, $this->mailManager->getConnection('ses'));
    }

    public function testMimeIdgeneratorIdrightIsSet(): void
    {
        $this->assertTrue(Swift_DependencyContainer::getInstance()->has('mime.idgenerator.idright'));
        $this->assertSame('narrowspark.com', Swift_DependencyContainer::getInstance()->lookup('mime.idgenerator.idright'));
    }

    public function testCustomMailer(): void
    {
        $mailer    = $this->mailManager->getConnection('custom');
        $transport = $mailer->getSwiftMailer()->getTransport();

        $this->assertInstanceOf(Mailer::class, $mailer);
        $this->assertInstanceOf(MandrillTransport::class, $transport);
    }

    public function testMailerCanGetGlobalAddresses(): void
    {
        $mailer   = $this->mailManager->getConnection('array');
        $excepted = [
            'address' => 'hello@example.com',
            'name'    => 'Example',
        ];

        $this->assertSame($excepted, NSA::getProperty($mailer, 'from'));
        $this->assertSame($excepted, NSA::getProperty($mailer, 'to'));
        $this->assertSame($excepted, NSA::getProperty($mailer, 'replyTo'));
    }

    public function testMailerWithAllAddedClasses(): void
    {
        $this->mailManager->setContainer($this->mock(ContainerInterface::class));
        $this->mailManager->setEventManager($this->mock(EventManagerContract::class));
        $this->mailManager->setViewFactory($this->mock(ViewFactoryContract::class));

        $mailer = $this->mailManager->getConnection('array');

        $this->assertInstanceOf(ContainerInterface::class, NSA::getProperty($mailer, 'container'));
        $this->assertInstanceOf(EventManagerContract::class, NSA::getProperty($mailer, 'eventManager'));
        $this->assertInstanceOf(ViewFactoryContract::class, NSA::getProperty($mailer, 'viewFactory'));
    }

    public function testExtend(): void
    {
        $this->mailManager->extend('mock', function () {
            return \Mockery::mock(MailerContract::class);
        });

        $this->assertInstanceOf(MailerContract::class, $this->mailManager->getConnection('mock'));
    }
}
