<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use org\bovigo\vfs\vfsStream;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Translation\Translator as TranslatorContract;
use Viserio\Component\Parser\Provider\ParserServiceProvider;
use Viserio\Component\Translation\Provider\TranslationServiceProvider;
use Viserio\Component\Translation\TranslationManager;

/**
 * @internal
 */
final class TranslatorServiceProviderTest extends MockeryTestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    private $file;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
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

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);
    }

    public function testProvider(): void
    {
        $container = new Container();
        $container->instance(PsrLoggerInterface::class, $this->mock(PsrLoggerInterface::class));
        $container->register(new TranslationServiceProvider());
        $container->register(new ParserServiceProvider());

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

        static::assertInstanceOf(TranslationManager::class, $container->get(TranslationManager::class));
        static::assertInstanceOf(TranslatorContract::class, $container->get('translator'));
        static::assertInstanceOf(TranslatorContract::class, $container->get(TranslatorContract::class));
    }
}
