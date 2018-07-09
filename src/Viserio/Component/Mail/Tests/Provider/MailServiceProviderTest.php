<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Mail\Mailer as MailerContract;
use Viserio\Component\Contract\Queue\QueueConnector as QueueContract;
use Viserio\Component\Events\Provider\EventsServiceProvider;
use Viserio\Component\Filesystem\Provider\FilesServiceProvider;
use Viserio\Component\Mail\MailManager;
use Viserio\Component\Mail\Provider\MailServiceProvider;
use Viserio\Component\Mail\TransportFactory;
use Viserio\Component\View\Provider\ViewServiceProvider;

/**
 * @internal
 */
final class MailServiceProviderTest extends MockeryTestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new FilesServiceProvider());
        $container->register(new ViewServiceProvider());
        $container->register(new EventsServiceProvider());
        $container->register(new MailServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'mail' => [
                    'connections' => [],
                ],
                'view' => [
                    'paths'      => [__DIR__],
                    'extensions' => ['php'],
                ],
            ],
        ]);
        $container->instance(LoggerInterface::class, $this->mock(LoggerInterface::class));

        static::assertInstanceOf(TransportFactory::class, $container->get(TransportFactory::class));
        static::assertInstanceOf(MailManager::class, $container->get(MailManager::class));
        static::assertInstanceOf(MailerContract::class, $container->get(MailerContract::class));
        static::assertInstanceOf(MailerContract::class, $container->get('mailer'));
    }

    // @ToDo fix #394
//    public function testProviderWithQueue(): void
//    {
//        $container = new Container();
//        $container->register(new FilesServiceProvider());
//        $container->register(new ViewServiceProvider());
//        $container->register(new MailServiceProvider());
//
//        $container->get(RepositoryContract::class)->setArray([
//            'viserio' => [
//                'mail' => [
//                    'connections' => [],
//                ],
//                'view' => [
//                    'paths'      => [__DIR__],
//                    'extensions' => ['php'],
//                ],
//            ],
//        ]);
//        $container->instance(QueueContract::class, $this->getMockBuilder(QueueContract::class)->getMock());
//
//        self::assertInstanceOf(QueueMailer::class, $container->get(MailerContract::class));
//        self::assertInstanceOf(QueueMailer::class, $container->get('mailer'));
//    }
}
