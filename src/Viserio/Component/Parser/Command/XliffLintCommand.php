<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Command;

use DOMDocument;
use Generator;
use SplFileInfo;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;
use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contract\Translation\Exception\InvalidArgumentException;

/**
 * Validates XLIFF files syntax and outputs encountered errors.
 *
 * Some of this code has been ported from Symfony. The original
 *
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Translation/Command/XliffLintCommand.php
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 */
class XliffLintCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'lint:xliff';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'lint:xliff
        [filename : A file or a directory or STDIN.]
        [--format=text : The output format. Supports `text` or `json`.]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Lints a XLIFF file and outputs encountered errors';

    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        $filename            = $this->argument('filename');
        $format              = $this->option('format');
        $displayCorrectFiles = $this->getOutput()->isVerbose();

        if (! $filename) {
            if (! $stdin = $this->getStdin()) {
                throw new \RuntimeException('Please provide a filename or pipe file content to STDIN.');
            }

            $this->display(array($this->validate($stdin)));

            return;
        }

        if (!$this->isReadable($filename)) {
            throw new \RuntimeException(sprintf('File or directory "%s" is not readable.', $filename));
        }

        $filesInfo = array();

        foreach ($this->getFiles($filename) as $file) {
            $filesInfo[] = $this->validate(file_get_contents($file), $file);
        }

        $this->display($io, $filesInfo);
    }

    /**
     * @param string $format
     * @param array  $files
     *
     * @throws \Viserio\Component\Contract\Translation\Exception\InvalidArgumentException
     *
     * @return string
     */
    protected function display(string $format, array $files): string
    {
        switch ($format) {
            case 'txt':
                return $this->displayTxt($files);
            case 'json':
                return $this->displayJson($files);
            default:
                throw new InvalidArgumentException(sprintf('The format "%s" is not supported.', $format));
        }
    }

    /**
     * @param string $fileOrDirectory
     *
     * @return \Generator
     */
    protected function getFiles(string $fileOrDirectory): Generator
    {
        if (\is_file($fileOrDirectory)) {
            yield new SplFileInfo($fileOrDirectory);

            return;
        }

        foreach (self::getDirectoryIterator($fileOrDirectory) as $file) {
            if (! \in_array($file->getExtension(), ['xlf', 'xliff'], true)) {
                continue;
            }

            yield $file;
        }
    }

    /**
     * @param string $content
     * @param null|string $file
     *
     * @return array
     */
    private function validate(string $content, ?string $file = null): array
    {
        $errors = [];

        // Avoid: Warning DOMDocument::loadXML(): Empty string supplied as input
        if (\trim($content) === '') {
            return ['file' => $file, 'valid' => true];
        }

        \libxml_use_internal_errors(true);

        $document = new DOMDocument();
        $document->loadXML($content);

        if (($targetLanguage = $this->getTargetLanguageFromFile($document)) !== null) {
            $expectedFileExtension = \sprintf('%s.xlf', \str_replace('-', '_', $targetLanguage));
            $realFileExtension = \explode('.', \basename($file), 2)[1] ?? '';

            if ($expectedFileExtension !== $realFileExtension) {
                $errors[] = [
                    'line' => -1,
                    'column' => -1,
                    'message' => \sprintf('There is a mismatch between the file extension ("%s") and the "%s" value used in the "target-language" attribute of the file.', $realFileExtension, $targetLanguage),
                ];
            }
        }

        $document->schemaValidate(__DIR__.'/../Resources/schemas/xliff-core-1.2-strict.xsd');

        foreach (\libxml_get_errors() as $xmlError) {
            $errors[] = array(
                'line' => $xmlError->line,
                'column' => $xmlError->column,
                'message' => \trim($xmlError->message),
            );
        }

        \libxml_clear_errors();
        \libxml_use_internal_errors(false);

        return ['file' => $file, 'valid' => 0 === \count($errors), 'messages' => $errors];
    }

    private function getStdin()
    {
        if (\ftell(\STDIN) !== 0) {
            return;
        }

        $inputs = '';

        while (! \feof(\STDIN)) {
            $inputs .= \fread(\STDIN, 1024);
        }

        return $inputs;
    }

    /**
     * Get the target language from file.
     *
     * @param \DOMDocument $xliffContents
     *
     * @return null|string
     */
    private function getTargetLanguageFromFile(DOMDocument $xliffContents): ?string
    {
        foreach ($xliffContents->getElementsByTagName('file')[0]->attributes ?? [] as $attribute) {
            if ($attribute->nodeName === 'target-language') {
                return $attribute->nodeValue;
            }
        }

        return null;
    }

    /**
     * @param string $directory
     *
     * @return \RecursiveIteratorIterator
     */
    private static function getDirectoryIterator(string $directory)
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
    }
}
