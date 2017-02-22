<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests;

use Mockery as Mock;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use org\bovigo\vfs\vfsStream;
use Psr\Log\LoggerInterface;
use Viserio\Component\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Component\Contracts\Translation\MessageCatalogue as MessageCatalogueContract;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Parsers\FileLoader;
use Viserio\Component\Parsers\TaggableParser;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;
use Viserio\Component\Translation\MessageSelector;
use Viserio\Component\Translation\PluralizationRules;
use Viserio\Component\Translation\TranslationManager;

class TranslationManagerTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    private $manager;

    /**
     * @var org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        parent::setUp();

        $this->root    = vfsStream::setup();
        $this->manager = new TranslationManager(
            new PluralizationRules(),
            new MessageSelector()
        );
    }

    public function testSetAndGetDirectories()
    {
        $this->manager->setDirectories([
            __DIR__ . '/stubs',
        ]);

        self::assertSame(
            self::normalizeDirectorySeparator(__DIR__ . '/stubs'),
            $this->manager->getDirectories()[0]
        );
    }

    public function testSetAndGetLoader()
    {
        $this->manager->setLoader($this->mock(LoaderContract::class));

        self::assertInstanceOf(LoaderContract::class, $this->manager->getLoader());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testImportToThrowException()
    {
        vfsStream::newFile('invalid.php')->withContent("<?php
declare(strict_types=1); return [
    'domain1' => [
        'foo' => 'bar',
    ],
    'domain2' => [
        'bar' => 'foo',
    ],
];")->at($this->root);

        $this->manager->setLoader($this->getFileLoader());

        $this->manager->import('invalid.php');
    }

    public function testImport()
    {
        vfsStream::newFile('en.php')->withContent("<?php
declare(strict_types=1); return [
    'lang' => 'en',
    'domain1' => [
        'foo' => 'bar',
    ],
    'domain2' => [
        'bar' => 'foo',
    ],
];")->at($this->root);

        $this->manager->setLoader($this->getFileLoader());

        $this->manager->import('en.php');

        self::assertInstanceOf(TranslatorContract::class, $this->manager->getTranslator('en'));
        self::assertSame('en', $this->manager->getTranslator('en')->getLocale());
        self::assertSame('en', $this->manager->getTranslator()->getLocale());
    }

    public function testImportWithDefaultFallback()
    {
        vfsStream::newFile('fr.php')->withContent("<?php
declare(strict_types=1); return [
    'lang' => 'fr',
    'domain1' => [
        'foo' => 'bar',
    ],
    'domain2' => [
        'bar' => 'foo',
    ],
];")->at($this->root);

        $message = $this->mock(MessageCatalogueContract::class);
        $message
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn('de');
        $message
            ->shouldReceive('setParent')
            ->once();

        $this->manager->setDefaultFallback($message);
        $this->manager->setLoader($this->getFileLoader());

        $this->manager->import('fr.php');
    }

    public function testImportWithLanguageFallback()
    {
        vfsStream::newFile('de.php')->withContent("<?php
declare(strict_types=1); return [
    'lang' => 'de',
    'domain1' => [
        'foo' => 'bar',
    ],
    'domain2' => [
        'bar' => 'foo',
    ],
];")->at($this->root);

        $message = $this->mock(MessageCatalogueContract::class);
        $message
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn('en');
        $message
            ->shouldReceive('setParent')
            ->once();

        $this->manager->setLanguageFallback('de', $message);
        $this->manager->setLoader($this->getFileLoader());

        $this->manager->import('de.php');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetTranslator()
    {
        $this->manager->getTranslator('jp');
    }

    public function testSetAndGetDefaultFallback()
    {
        $this->manager->setDefaultFallback($this->mock(MessageCatalogueContract::class));

        self::assertInstanceOf(MessageCatalogueContract::class, $this->manager->getDefaultFallback());
    }

    public function testSetAndLanguageFallback()
    {
        $this->manager->setLanguageFallback('de', $this->mock(MessageCatalogueContract::class));

        self::assertInstanceOf(MessageCatalogueContract::class, $this->manager->getLanguageFallback('de'));
    }

    public function testSetAndGetLocale()
    {
        $this->manager->setLocale('de');

        self::assertSame('de', $this->manager->getLocale());
    }

    public function testGetPluralization()
    {
        self::assertInstanceOf(PluralizationRules::class, $this->manager->getPluralization());
    }

    public function testSetAndGetLogger()
    {
        $this->manager->setLogger($this->mock(LoggerInterface::class));

        self::assertInstanceOf(LoggerInterface::class, $this->manager->getLogger());

        $message = $this->mock(MessageCatalogueContract::class);
        $message
            ->shouldReceive('getLocale')
            ->times(3)
            ->andReturn('en');

        $this->manager->addMessageCatalogue($message);
    }

    protected function getFileLoader()
    {
        return new FileLoader(
            new TaggableParser(
                new Filesystem()
            ),
            [
                $this->root->url(),
            ]
        );
    }
}
