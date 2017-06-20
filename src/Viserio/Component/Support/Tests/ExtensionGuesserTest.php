<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Support\ExtensionGuesser;
use Viserio\Component\Contracts\Support\Exception\AccessDeniedException;

class ExtensionGuesserTest extends TestCase
{
    public function testRegisterNewGuesser()
    {
        $file = __DIR__.'/Fixture/test';
        $guesser = $this->createMockGuesser($file, 'gif');

        ExtensionGuesser::register($guesser);

        $this->assertEquals('gif', ExtensionGuesser::guess($file));
    }

    public function testGuessExtensionIsBasedOnMimeType()
    {
        $this->assertEquals('gif', ExtensionGuesser::guess(__DIR__.'/Fixture/test'));
    }

    /**
     * @requires extension fileinfo
     */
    public function testGuessExtensionWithFileinfo()
    {
        $this->assertEquals(
            'inode/x-empty',
            ExtensionGuesser::getFileinfoMimeTypeGuess(__DIR__.'/Fixture/other-file.example')
        );

        ExtensionGuesser::flush();
        ExtensionGuesser::register([ExtensionGuesser::class, 'getFileinfoMimeTypeGuess']);

        $this->assertEquals('image/gif', ExtensionGuesser::guess(__DIR__.'/Fixture/test.gif'));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unable to guess the mime type as no guessers are available.
     */
    public function testGuessWithIncorrectPath()
    {
        ExtensionGuesser::flush();

        ExtensionGuesser::guess(__DIR__.'/Fixture/test.gif');
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Support\Exception\FileNotFoundException
     */
    public function testGuessExtensionToThrowExceptionIfNoFileFound()
    {
        ExtensionGuesser::guess(__DIR__.'/Fixture/test---');
    }

    public function testGuessFileWithUnknownExtension()
    {
        ExtensionGuesser::register([ExtensionGuesser::class, 'getFileBinaryMimeTypeGuess']);
        ExtensionGuesser::register([ExtensionGuesser::class, 'getFileinfoMimeTypeGuess']);

        $this->assertEquals('application/octet-stream', ExtensionGuesser::guess(__DIR__.'/Fixture/.unknownextension'));

        ExtensionGuesser::flush();
    }

    public function testGuessWithNonReadablePath()
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('Can not verify chmod operations on Windows');
        }

        if (!getenv('USER') || 'root' === getenv('USER')) {
            $this->markTestSkipped('This test will fail if run under superuser');
        }

        $path = __DIR__.'/Fixture/to_delete';
        touch($path);
        @chmod($path, 0333);

        if (substr(sprintf('%o', fileperms($path)), -4) == '0333') {
            $this->expectException(AccessDeniedException::class);

            ExtensionGuesser::register([ExtensionGuesser::class, 'getFileBinaryMimeTypeGuess']);
            ExtensionGuesser::register([ExtensionGuesser::class, 'getFileinfoMimeTypeGuess']);
            ExtensionGuesser::guess($path);
        } else {
            $this->markTestSkipped('Can not verify chmod operations, change of file permissions failed');
        }
    }

    public static function tearDownAfterClass()
    {
        $path = __DIR__.'/Fixture/to_delete';

        if (file_exists($path)) {
            @chmod($path, 0666);
            @unlink($path);
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
