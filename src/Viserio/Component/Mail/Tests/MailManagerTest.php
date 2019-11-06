<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Mail\Tests;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Nyholm\NSA;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Swift_DependencyContainer;
use Viserio\Component\Mail\Mailer;
use Viserio\Component\Mail\MailManager;
use Viserio\Component\Mail\Transport\MandrillTransport;
use Viserio\Component\Mail\TransportFactory;
use Viserio\Contract\Events\EventManager as EventManagerContract;
use Viserio\Contract\Mail\Mailer as MailerContract;
use Viserio\Contract\Manager\Exception\InvalidArgumentException;
use Viserio\Contract\View\Factory as ViewFactoryContract;

/**
 * @internal
 *
 * @small
 */
final class MailManagerTest extends MockeryTestCase
{
    /** @var \Viserio\Component\Mail\MailManager */
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
                    'from' => [
                        'address' => 'hello@example.com',
                        'name' => 'Example',
                    ],
                    'to' => [
                        'address' => 'hello@example.com',
                        'name' => 'Example',
                    ],
                    'reply_to' => [
                        'address' => 'hello@example.com',
                        'name' => 'Example',
                    ],
                    'connections' => [
                        'smtp' => [
                            'transporter' => [
                                'host' => '',
                                'port' => '',
                                'encryption' => '',
                                'username' => '',
                                'password' => '',
                                'stream' => '',
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
                                'key' => 'test',
                                'region' => 'us-west-2',
                            ],
                        ],
                        'custom' => [
                            'driver' => 'mandrill',
                            'transporter' => [
                                'secret' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $factory = new TransportFactory();
        $factory->setLogger(Mockery::mock(LoggerInterface::class));

        $this->mailManager = new MailManager($config, $factory);
    }

    public function testGetDefaultConnection(): void
    {
        self::assertSame('array', $this->mailManager->getDefaultConnection());
    }

    public function testDefaultConnection(): void
    {
        self::assertInstanceOf(Mailer::class, $this->mailManager->getConnection());
    }

    public function testGetConnectionThrowAException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mailer [test] is not supported.');

        $this->mailManager->getConnection('test');
    }

    public function testLogMailer(): void
    {
        self::assertInstanceOf(Mailer::class, $this->mailManager->getConnection('log'));
    }

    public function testSendmailMailer(): void
    {
        self::assertInstanceOf(Mailer::class, $this->mailManager->getConnection('sendmail'));
    }

    public function testArrayMailer(): void
    {
        self::assertInstanceOf(Mailer::class, $this->mailManager->getConnection('array'));
    }

    public function testSmtpMailer(): void
    {
        self::assertInstanceOf(Mailer::class, $this->mailManager->getConnection('smtp'));
    }

    public function testMailgunMailer(): void
    {
        self::assertInstanceOf(Mailer::class, $this->mailManager->getConnection('mailgun'));
    }

    public function testMandrillMailer(): void
    {
        self::assertInstanceOf(Mailer::class, $this->mailManager->getConnection('mandrill'));
    }

    public function testSparkPostMailer(): void
    {
        self::assertInstanceOf(Mailer::class, $this->mailManager->getConnection('sparkpost'));
    }

    public function testSesMailer(): void
    {
        self::assertInstanceOf(Mailer::class, $this->mailManager->getConnection('ses'));
    }

    public function testMimeIdgeneratorIdrightIsSet(): void
    {
        self::assertTrue(Swift_DependencyContainer::getInstance()->has('mime.idgenerator.idright'));
        self::assertSame('narrowspark.com', Swift_DependencyContainer::getInstance()->lookup('mime.idgenerator.idright'));
    }

    public function testCustomMailer(): void
    {
        $mailer = $this->mailManager->getConnection('custom');
        $transport = $mailer->getSwiftMailer()->getTransport();

        self::assertInstanceOf(Mailer::class, $mailer);
        self::assertInstanceOf(MandrillTransport::class, $transport);
    }

    public function testMailerCanGetGlobalAddresses(): void
    {
        $mailer = $this->mailManager->getConnection('array');
        $excepted = [
            'address' => 'hello@example.com',
            'name' => 'Example',
        ];

        self::assertSame($excepted, NSA::getProperty($mailer, 'from'));
        self::assertSame($excepted, NSA::getProperty($mailer, 'to'));
        self::assertSame($excepted, NSA::getProperty($mailer, 'replyTo'));
    }

    public function testMailerWithAllAddedClasses(): void
    {
        $this->mailManager->setContainer(Mockery::mock(ContainerInterface::class));
        $this->mailManager->setEventManager(Mockery::mock(EventManagerContract::class));
        $this->mailManager->setViewFactory(Mockery::mock(ViewFactoryContract::class));

        $mailer = $this->mailManager->getConnection('array');

        self::assertInstanceOf(ContainerInterface::class, NSA::getProperty($mailer, 'container'));
        self::assertInstanceOf(EventManagerContract::class, NSA::getProperty($mailer, 'eventManager'));
        self::assertInstanceOf(ViewFactoryContract::class, NSA::getProperty($mailer, 'viewFactory'));
    }

    public function testExtend(): void
    {
        $this->mailManager->extend('mock', function () {
            return Mockery::mock(MailerContract::class);
        });

        self::assertInstanceOf(MailerContract::class, $this->mailManager->getConnection('mock'));
    }
}
