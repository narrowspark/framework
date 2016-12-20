<?php
declare(strict_types=1);
namespace Viserio\Mail\Tests\Providers;

use Swift_Mailer;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Mail\Mailer as MailerContract;
use Viserio\Contracts\Queue\Queue as QueueContract;
use Viserio\Events\Providers\EventsServiceProvider;
use Viserio\Filesystem\Providers\FilesServiceProvider;
use Viserio\Mail\Mailer;
use Viserio\Mail\Providers\MailServiceProvider;
use Viserio\Mail\QueueMailer;
use Viserio\Mail\TransportManager;
use Viserio\View\Providers\ViewServiceProvider;

class MailServiceProviderTest extends \PHPUnit_Framework_TestCase
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
