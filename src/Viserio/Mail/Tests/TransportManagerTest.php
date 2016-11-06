<?php
declare(strict_types=1);
namespace Viserio\Mail\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Log\LoggerInterface;
use Swift_MailTransport;
use Swift_SendmailTransport;
use Swift_SmtpTransport;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Mail\Transport\Log as LogTransport;
use Viserio\Mail\Transport\Mailgun as MailgunTransport;
use Viserio\Mail\Transport\Mandrill as MandrillTransport;
use Viserio\Mail\Transport\Ses as SesTransport;
use Viserio\Mail\Transport\SparkPost as SparkPostTransport;
use Viserio\Mail\TransportManager;

class TransportManagerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    /**
     * @var \Viserio\Mail\TransportManager
     */
    protected $manager;

    public function setUp()
    {
        $this->manager = new TransportManager($this->mock(ConfigContract::class));
    }

    public function testLogTransporter()
    {
        $manager = $this->manager;
        $manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('mail.drivers', []);
        $manager->setContainer(new ArrayContainer([
            LoggerInterface::class => $this->mock(LoggerInterface::class),
        ]));

        $this->assertInstanceOf(LogTransport::class, $manager->driver('log'));
    }

    public function testMailTransport()
    {
        $manager = $this->manager;
        $manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('mail.drivers', []);
        $this->assertInstanceOf(Swift_MailTransport::class, $manager->driver('mail'));
    }

    public function testSendmailTransport()
    {
        $manager = $this->manager;
        $manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('mail.drivers', []);
        $this->assertInstanceOf(Swift_SendmailTransport::class, $manager->driver('sendmail'));
    }

    public function testSmtpTransport()
    {
        $manager = $this->manager;
        $manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('mail.drivers', [])
            ->andReturn([
                'smtp' => [
                    'host' => '',
                    'port' => '',
                    'encryption' => '',
                    'username' => '',
                    'password' => '',
                    'stream' => '',
                ],
            ]);

        $this->assertInstanceOf(Swift_SmtpTransport::class, $manager->driver('smtp'));
    }

    public function testMailgunTransport()
    {
        $manager = $this->manager;
        $manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('mail.drivers', [])
            ->andReturn([
                'mailgun' => [
                    'secret' => '',
                    'domain' => '',
                ],
            ]);

        $this->assertInstanceOf(MailgunTransport::class, $manager->driver('mailgun'));
    }

    public function testMandrillTransport()
    {
        $manager = $this->manager;
        $manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('mail.drivers', [])
            ->andReturn([
                'mandrill' => [
                    'secret' => '',
                ],
            ]);

        $this->assertInstanceOf(MandrillTransport::class, $manager->driver('mandrill'));
    }

    public function testSparkPostTransport()
    {
        $manager = $this->manager;
        $manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('mail.drivers', [])
            ->andReturn([
                'sparkpost' => [
                    'secret' => '',
                ],
            ]);

        $this->assertInstanceOf(SparkPostTransport::class, $manager->driver('sparkpost'));
    }

    public function testSesTransport()
    {
        $manager = $this->manager;
        $manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('mail.drivers', [])
            ->andReturn([
                'ses' => [
                    'secret' => '',
                    'key' => '',
                    'region' => 'us-west-2',
                ],
            ]);

        $this->assertInstanceOf(SesTransport::class, $manager->driver('ses'));
    }
}
