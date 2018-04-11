<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Parser\Command\XliffLintCommand;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * Validates XLIFF files syntax and outputs encountered errors.
 *
 * Some of this code has been ported from Symfony. The original
 *
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Translation/Tests/Command/XliffLintCommandTest.php
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 */
class XliffLintCommandTest extends TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var \Viserio\Component\Parser\Command\XliffLintCommand
     */
    private $command;

    /**
     * @var array
     */
    private $files;

    /**
     * @var string
     */
    private $path;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->path = self::normalizeDirectorySeparator(__DIR__ . '/xliff-lint-test');

        \mkdir($this->path);

        $this->files   = [];
        $this->command = new XliffLintCommand();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            if (\file_exists($file)) {
                \unlink($file);
            }
        }

        \rmdir($this->path);
    }

    public function testLintCommandIncorrectTargetLanguage(): void
    {
        $tester   = new CommandTester($this->command);
        $filename = $this->createFile('note', 'es');

        $tester->execute(['filename' => $filename], ['decorated' => false]);

        self::assertEquals(1, $tester->getStatusCode(), 'Returns 1 in case of error');
        self::assertContains('There is a mismatch between the file extension ("en.xlf") and the "es" value used in the "target-language" attribute of the file.', \trim($tester->getDisplay()));
    }

    /**
     * @param string $sourceContent
     * @param string $targetLanguage
     *
     * @return string Path to the new file
     */
    private function createFile(string $sourceContent = 'note', string $targetLanguage = 'en')
    {
        $xliffContent = <<<'XLIFF'
<?xml version="1.0"?>
<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
    <file source-language="en" target-language="$targetLanguage" datatype="plaintext" original="file.ext">
        <body>
            <trans-unit id="note">
                <source>$sourceContent</source>
                <target>NOTE</target>
            </trans-unit>
        </body>
    </file>
</xliff>
XLIFF;
        $filename = self::normalizeDirectorySeparator($this->path . '/messages.en.xlf');

        \file_put_contents($filename, $xliffContent);

        $this->files[] = $filename;

        return $filename;
    }
}
