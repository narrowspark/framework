<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\Providers;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Component\Parsers\Providers\ParsersServiceProvider;
use Viserio\Component\Translation\Providers\TranslationServiceProvider;
use Viserio\Component\Translation\TranslationManager;

class TranslatorServiceProviderTest extends TestCase
{
    use MockeryTrait;

    /**
     * @var org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    private $file;

    public function setUp()
    {
        $this->root = vfsStream::setup();
        $this->file = vfsStream::newFile('temp.php')->withContent(
            '<?php
declare(strict_types=1);

return [
    "lang" => "en",
    "message" => [
        "Hallo" => "hallo",
    ]
];
            '
        )->at($this->root);
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testProvider()
    {
        $container = new Container();
        $container->instance(PsrLoggerInterface::class, $this->mock(PsrLoggerInterface::class));
        $container->register(new TranslationServiceProvider());
        $container->register(new ParsersServiceProvider());
        $container->register(new ConfigServiceProvider());

        $container->get('config')->set('translation', [
            'locale'      => 'en',
            'files'       => $this->file->url(),
            'directories' => [
                __DIR__,
            ],
        ]);

        self::assertInstanceOf(TranslationManager::class, $container->get(TranslationManager::class));
        self::assertInstanceOf(TranslatorContract::class, $container->get('translator'));
        self::assertInstanceOf(TranslatorContract::class, $container->get(TranslatorContract::class));
    }

    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new ParsersServiceProvider());
        $container->register(new TranslationServiceProvider());

        $container->instance('options', [
            'locale' => 'en',
            'files'  => $this->file->url(),
        ]);

        self::assertInstanceOf(TranslationManager::class, $container->get(TranslationManager::class));
    }

    public function testProviderWithoutConfigManagerAndNamespace()
    {
        $container = new Container();
        $container->register(new ParsersServiceProvider());
        $container->register(new TranslationServiceProvider());

        $container->instance('viserio.translation.options', [
            'locale' => 'en',
            'files'  => $this->file->url(),
        ]);

        self::assertInstanceOf(TranslationManager::class, $container->get(TranslationManager::class));
    }
}
