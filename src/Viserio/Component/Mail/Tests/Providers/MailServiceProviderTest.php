<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Swift_Mailer;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Mail\Mailer as MailerContract;
use Viserio\Component\Contracts\Queue\Queue as QueueContract;
use Viserio\Component\Events\Providers\EventsServiceProvider;
use Viserio\Component\Filesystem\Providers\FilesServiceProvider;
use Viserio\Component\Mail\Mailer;
use Viserio\Component\Mail\Providers\MailServiceProvider;
use Viserio\Component\Mail\QueueMailer;
use Viserio\Component\Mail\TransportManager;
use Viserio\Component\View\Providers\ViewServiceProvider;

class MailServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new EventsServiceProvider());
        $container->register(new MailServiceProvider());

        $container->get(RepositoryContract::class)->set('mail', [
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
        ]);

        self::assertInstanceOf(MailerContract::class, $container->get(MailerContract::class));
        self::assertInstanceOf(MailerContract::class, $container->get(Mailer::class));
        self::assertInstanceOf(MailerContract::class, $container->get('mailer'));
        self::assertInstanceOf(TransportManager::class, $container->get(TransportManager::class));
        self::assertInstanceOf(Swift_Mailer::class, $container->get(Swift_Mailer::class));
    }

    public function testProviderWithQueue()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new MailServiceProvider());
        $container->register(new FilesServiceProvider());
        $container->register(new ViewServiceProvider());

        $container->get(RepositoryContract::class)->set('mail', ['drivers' => [
            'smtp' => [
                'host' => 'smtp.mailgun.org',
                'port' => '25',
            ],
        ]]);
        $container->instance(QueueContract::class, $this->getMockBuilder(QueueContract::class)->getMock());

        self::assertInstanceOf(QueueMailer::class, $container->get(MailerContract::class));
        self::assertInstanceOf(QueueMailer::class, $container->get(Mailer::class));
        self::assertInstanceOf(QueueMailer::class, $container->get('mailer'));
    }
}
