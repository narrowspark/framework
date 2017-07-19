<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Swift_Mailer;
use Viserio\Component\Config\Provider\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Mail\Mailer as MailerContract;
use Viserio\Component\Contracts\Queue\QueueConnector as QueueContract;
use Viserio\Component\Events\Provider\EventsServiceProvider;
use Viserio\Component\Filesystem\Provider\FilesServiceProvider;
use Viserio\Component\Mail\Mailer;
use Viserio\Component\Mail\Provider\MailServiceProvider;
use Viserio\Component\Mail\QueueMailer;
use Viserio\Component\Mail\TransportManager;
use Viserio\Component\View\Provider\ViewServiceProvider;

class MailServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new EventsServiceProvider());
        $container->register(new MailServiceProvider());

        $container->get(RepositoryContract::class)->setArray([
            'viserio' => [
                'mail' => [
                    'drivers' => [
                        'smtp' => [
                            'host' => 'smtp.mailgun.org',
                            'port' => '25',
                        ],
                    ],
                    'from' => [
                        'address' => '',
                        'name'    => '',
                    ],
                    'to' => [
                        'address' => '',
                        'name'    => '',
                    ],
                ],
                'view' => [
                    'paths'      => [__DIR__],
                    'extensions' => ['php'],
                ],
            ],
        ]);

        self::assertInstanceOf(MailerContract::class, $container->get(MailerContract::class));
        self::assertInstanceOf(MailerContract::class, $container->get(Mailer::class));
        self::assertInstanceOf(MailerContract::class, $container->get('mailer'));
        self::assertInstanceOf(TransportManager::class, $container->get(TransportManager::class));
        self::assertInstanceOf(Swift_Mailer::class, $container->get(Swift_Mailer::class));
    }

    public function testProviderWithQueue(): void
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new MailServiceProvider());
        $container->register(new FilesServiceProvider());
        $container->register(new ViewServiceProvider());

        $container->get(RepositoryContract::class)->setArray([
            'viserio' => [
                'mail' => [
                    'drivers' => [
                        'smtp' => [
                            'host' => 'smtp.mailgun.org',
                            'port' => '25',
                        ],
                    ],
                ],
                'view' => [
                    'paths'      => [__DIR__],
                    'extensions' => ['php'],
                ],
            ],
        ]);
        $container->instance(QueueContract::class, $this->getMockBuilder(QueueContract::class)->getMock());

        self::assertInstanceOf(QueueMailer::class, $container->get(MailerContract::class));
        self::assertInstanceOf(QueueMailer::class, $container->get(Mailer::class));
        self::assertInstanceOf(QueueMailer::class, $container->get('mailer'));
    }
}
