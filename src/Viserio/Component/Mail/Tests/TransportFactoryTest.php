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
use Psr\Log\LoggerInterface;
use Swift_SendmailTransport;
use Swift_SmtpTransport;
use Swift_Transport;
use Viserio\Component\Mail\Transport\ArrayTransport;
use Viserio\Component\Mail\Transport\LogTransport;
use Viserio\Component\Mail\Transport\MailgunTransport;
use Viserio\Component\Mail\Transport\MandrillTransport;
use Viserio\Component\Mail\Transport\SesTransport;
use Viserio\Component\Mail\Transport\SparkPostTransport;
use Viserio\Component\Mail\TransportFactory;
use Viserio\Contract\Mail\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class TransportFactoryTest extends MockeryTestCase
{
    /** @var \Viserio\Component\Mail\TransportFactory */
    private $factory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new TransportFactory();
    }

    public function testLogTransporter(): void
    {
        $this->factory->setLogger(Mockery::mock(LoggerInterface::class));

        self::assertInstanceOf(LogTransport::class, $this->factory->getTransport('log', []));
    }

    public function testSendmailTransport(): void
    {
        self::assertInstanceOf(Swift_SendmailTransport::class, $this->factory->getTransport('sendmail', []));
    }

    public function testSmtpTransport(): void
    {
        self::assertInstanceOf(
            Swift_SmtpTransport::class,
            $this->factory->getTransport(
                'smtp',
                [
                    'host' => '',
                    'port' => '',
                    'encryption' => '',
                    'username' => '',
                    'password' => '',
                    'stream' => '',
                ]
            )
        );
    }

    public function testMailgunTransport(): void
    {
        self::assertInstanceOf(MailgunTransport::class, $this->factory->getTransport('mailgun', ['secret' => '', 'domain' => '']));
    }

    public function testMandrillTransport(): void
    {
        self::assertInstanceOf(MandrillTransport::class, $this->factory->getTransport('mandrill', ['secret' => '']));
    }

    public function testSparkPostTransport(): void
    {
        self::assertInstanceOf(SparkPostTransport::class, $this->factory->getTransport('sparkpost', ['secret' => '']));
    }

    public function testSesTransport(): void
    {
        self::assertInstanceOf(
            SesTransport::class,
            $this->factory->getTransport(
                'ses',
                [
                    'secret' => 'test',
                    'key' => 'test',
                    'region' => 'us-west-2',
                ]
            )
        );
    }

    public function testArrayTransport(): void
    {
        self::assertInstanceOf(ArrayTransport::class, $this->factory->getTransport('array', []));
    }

    public function testGetTransports(): void
    {
        self::assertCount(0, $this->factory->getTransports());

        $this->factory->getTransport('array', []);

        self::assertCount(1, $this->factory->getTransports());
    }

    public function testGetTransportThrowError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Transport [test] is not supported.');

        self::assertInstanceOf(ArrayTransport::class, $this->factory->getTransport('test', []));
    }

    public function testExtend(): void
    {
        $this->factory->extend('public', function () {
            return Mockery::mock(Swift_Transport::class);
        });

        self::assertInstanceOf(Swift_Transport::class, $this->factory->getTransport('public', []));
    }
}
