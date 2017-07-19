<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Exception\AccessDeniedException;
use Viserio\Component\Http\ExtensionGuesser;

class ExtensionGuesserTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        $path = __DIR__ . '/Fixture/to_delete';

        if (\file_exists($path)) {
            @\chmod($path, 0666);
            @\unlink($path);
        }
    }

    public function testRegisterNewGuesser(): void
    {
        $file    = __DIR__ . '/Fixture/test';
        $guesser = $this->createMockGuesser($file, 'gif');

        ExtensionGuesser::register($guesser);

        $this->assertEquals('gif', ExtensionGuesser::guess($file));
    }

    public function testGuessExtensionIsBasedOnMimeType(): void
    {
        $this->assertEquals('gif', ExtensionGuesser::guess(__DIR__ . '/Fixture/test'));
    }

    /**
     * @requires extension fileinfo
     */
    public function testGuessExtensionWithFileinfo(): void
    {
        $this->assertEquals(
            'inode/x-empty',
            ExtensionGuesser::getFileinfoMimeTypeGuess(__DIR__ . '/Fixture/other-file.example')
        );

        ExtensionGuesser::flush();
        ExtensionGuesser::register([ExtensionGuesser::class, 'getFileinfoMimeTypeGuess']);

        $this->assertEquals('image/gif', ExtensionGuesser::guess(__DIR__ . '/Fixture/test.gif'));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unable to guess the mime type as no guessers are available.
     */
    public function testGuessWithIncorrectPath(): void
    {
        ExtensionGuesser::flush();

        ExtensionGuesser::guess(__DIR__ . '/Fixture/test.gif');
    }

    /**
     * @expectedException \Viserio\Component\Http\Exception\FileNotFoundException
     */
    public function testGuessExtensionToThrowExceptionIfNoFileFound(): void
    {
        ExtensionGuesser::guess(__DIR__ . '/Fixture/test---');
    }

    public function testGuessFileWithUnknownExtension(): void
    {
        ExtensionGuesser::register([ExtensionGuesser::class, 'getFileBinaryMimeTypeGuess']);
        ExtensionGuesser::register([ExtensionGuesser::class, 'getFileinfoMimeTypeGuess']);

        $this->assertEquals('application/octet-stream', ExtensionGuesser::guess(__DIR__ . '/Fixture/.unknownextension'));

        ExtensionGuesser::flush();
    }

    public function testGuessWithNonReadablePath(): void
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('Can not verify chmod operations on Windows');
        }

        if (! \getenv('USER') || 'root' === \getenv('USER')) {
            $this->markTestSkipped('This test will fail if run under superuser');
        }

        $path = __DIR__ . '/Fixture/to_delete';
        \touch($path);
        @\chmod($path, 0333);

        if (\mb_substr(\sprintf('%o', \fileperms($path)), -4) == '0333') {
            $this->expectException(AccessDeniedException::class);

            ExtensionGuesser::register([ExtensionGuesser::class, 'getFileBinaryMimeTypeGuess']);
            ExtensionGuesser::register([ExtensionGuesser::class, 'getFileinfoMimeTypeGuess']);
            ExtensionGuesser::guess($path);
        } else {
            $this->markTestSkipped('Can not verify chmod operations, change of file permissions failed');
        }
    }

    protected function createMockGuesser($path, $mimeType)
    {
        return function ($givenPath) use ($path, $mimeType) {
            self::assertSame($givenPath, $path);

            return $mimeType;
        };
    }
}
