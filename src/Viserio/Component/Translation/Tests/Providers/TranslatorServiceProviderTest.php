<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\Providers;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use org\bovigo\vfs\vfsStream;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Component\Parsers\Providers\ParsersServiceProvider;
use Viserio\Component\Translation\Providers\TranslationServiceProvider;
use Viserio\Component\Translation\TranslationManager;

class TranslatorServiceProviderTest extends MockeryTestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    private $file;

    public function setUp()
    {
        parent::setUp();

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
    }

    public function testProvider()
    {
        $container = new Container();
        $container->instance(PsrLoggerInterface::class, $this->mock(PsrLoggerInterface::class));
        $container->register(new TranslationServiceProvider());
        $container->register(new ParsersServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'translation' => [
                    'locale'      => 'en',
                    'files'       => $this->file->url(),
                    'directories' => [
                        __DIR__,
                    ],
                ],
            ],
        ]);

        self::assertInstanceOf(TranslationManager::class, $container->get(TranslationManager::class));
        self::assertInstanceOf(TranslatorContract::class, $container->get('translator'));
        self::assertInstanceOf(TranslatorContract::class, $container->get(TranslatorContract::class));
    }
}
