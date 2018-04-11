<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Command;

use DOMDocument;
use FilesystemIterator;
use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contract\Translation\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Translation\Exception\RuntimeException;
use Viserio\Component\Parser\Traits\GetXliffSchemaTrait;
use Viserio\Component\Parser\Traits\GetXliffVersionNumberTrait;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

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
    use NormalizePathAndDirectorySeparatorTrait;
    use GetXliffVersionNumberTrait;
    use GetXliffSchemaTrait;

    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'lint:xliff';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'lint:xliff
        [filename : A file or a directory or STDIN.]
        [--format=txt : The output format. Supports `txt` or `json`.]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Lints a XLIFF file and outputs encountered errors';

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Component\Contract\Translation\Exception\InvalidArgumentException
     * @throws \Viserio\Component\Contract\Translation\Exception\RuntimeException
     */
    public function handle(): int
    {
        $filename            = $this->argument('filename');
        $format              = $this->option('format');
        $displayCorrectFiles = $this->getOutput()->isVerbose();

        if (! $filename) {
            if (! $stdin = $this->getStdin()) {
                throw new RuntimeException('Please provide a filename or pipe file content to STDIN.');
            }

            return $this->display([$this->validate($stdin)], $format, $displayCorrectFiles);
        }

        if (! \is_readable($filename)) {
            throw new RuntimeException(\sprintf('File or directory "%s" is not readable.', $filename));
        }

        $filesInfo = [];

        foreach ($this->getFiles($filename) as $file) {
            $filesInfo[] = $this->validate(\file_get_contents($file), $file);
        }

        return $this->display($filesInfo, $format, $displayCorrectFiles);
    }

    /**
     * @param string $format
     * @param array  $files
     * @param bool   $displayCorrectFiles
     *
     * @throws \Viserio\Component\Contract\Translation\Exception\InvalidArgumentException
     *
     * @return int
     */
    protected function display(array $files, string $format, bool $displayCorrectFiles): int
    {
        switch ($format) {
            case 'txt':
                return $this->displayTxt($files, $displayCorrectFiles);
            case 'json':
                return $this->displayJson($files);
            default:
                throw new InvalidArgumentException(\sprintf('The format "%s" is not supported.', $format));
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
            yield $fileOrDirectory;

            return;
        }

        foreach (self::getDirectoryIterator($fileOrDirectory) as $file) {
            if (! \in_array($file->getExtension(), ['xlf', 'xliff'], true)) {
                continue;
            }

            yield (string) $file;
        }
    }

    /**
     * Validate xliff on v1/2 xliff schema.
     *
     * @param string      $content
     * @param null|string $file
     *
     * @return array
     */
    private function validate(string $content, ?string $file = null): array
    {
        // Avoid: Warning DOMDocument::loadXML(): Empty string supplied as input
        if (\trim($content) === '') {
            return ['file' => $file, 'valid' => true];
        }

        $internalErrors  = \libxml_use_internal_errors(true);

        $document = new DOMDocument();
        $document->loadXML($content);

        $errors         = [];
        $targetLanguage = $this->getTargetLanguageFromFile($document);

        if ($targetLanguage !== null) {
            $expectedFileExtension = \sprintf('%s.xlf', \str_replace('-', '_', $targetLanguage));
            $realFileExtension     = \explode('.', \basename($file), 2)[1] ?? '';

            if ($expectedFileExtension !== $realFileExtension) {
                $errors[] = [
                    'line'    => -1,
                    'column'  => -1,
                    'message' => \sprintf('There is a mismatch between the file extension ("%s") and the "%s" value used in the "target-language" attribute of the file.', $realFileExtension, $targetLanguage),
                ];
            }
        }

        $document->schemaValidateSource(self::getXliffSchema(self::getXliffVersionNumber($document)));

        foreach (\libxml_get_errors() as $error) {
            $errors[] = [
                'line'    => $error->line,
                'column'  => $error->column,
                'message' => \trim($error->message),
            ];
        }

        \libxml_clear_errors();
        \libxml_use_internal_errors($internalErrors);

        return ['file' => $file, 'valid' => 0 === \count($errors), 'messages' => $errors];
    }

    /**
     * Display errors in txt format.
     *
     * @param array $filesInfo
     * @param bool  $displayCorrectFiles
     *
     * @return int
     */
    private function displayTxt(array $filesInfo, bool $displayCorrectFiles): int
    {
        $countFiles   = \count($filesInfo);
        $erroredFiles = 0;
        $output       = $this->getOutput();

        foreach ($filesInfo as $info) {
            if ($displayCorrectFiles && $info['valid']) {
                $output->comment('<info>OK</info>' . ($info['file'] ? \sprintf(' in %s', $info['file']) : ''));
            } elseif (! $info['valid']) {
                $erroredFiles++;

                $output->text('<error>ERROR</error>' . ($info['file'] ? \sprintf(' in %s', $info['file']) : ''));

                $output->listing(\array_map(function ($error) {
                    // general document errors have a '-1' line number
                    return $error['line'] === -1 ? $error['message'] : \sprintf('Line %d, Column %d: %s', $error['line'], $error['column'], $error['message']);
                }, $info['messages']));
            }
        }

        if ($erroredFiles === 0) {
            $this->getOutput()->success(\sprintf('All %d XLIFF files contain valid syntax.', $countFiles));
        } else {
            $this->warn(\sprintf('%d XLIFF files have valid syntax and %d contain errors.', $countFiles - $erroredFiles, $erroredFiles));
        }

        return \min($erroredFiles, 1);
    }

    /**
     * Display errors in json format.
     *
     * @param array $filesInfo
     *
     * @return int
     */
    private function displayJson(array $filesInfo): int
    {
        $errors = 0;

        \array_walk($filesInfo, function (&$v) use (&$errors): void {
            $v['file'] = (string) $v['file'];

            if (! $v['valid']) {
                $errors++;
            }
        });

        $this->getOutput()->writeln(\json_encode($filesInfo, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES));

        return \min($errors, 1);
    }

    /**
     * @return null|string
     */
    private function getStdin(): ?string
    {
        if (\ftell(\STDIN) !== 0) {
            return null;
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
    private static function getDirectoryIterator(string $directory): RecursiveIteratorIterator
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
    }
}
