<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Command;

use DOMDocument;
use Viserio\Component\Parser\Utils\XliffUtils;

/**
 * Validates XLIFF files syntax and outputs encountered errors.
 *
 * Some of this code has been ported from Symfony. The original
 *
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Translation/Command/XliffLintCommand.php
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 */
class XliffLintCommand extends AbstractLintCommand
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
        [--format=txt : The output format. Supports `txt` or `json`.]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Lints a XLIFF file and outputs encountered errors.';

    /**
     * {@inheritdoc}
     */
    protected function validate(string $content, ?string $file = null): array
    {
        // Avoid: Warning DOMDocument::loadXML(): Empty string supplied as input
        if (\trim($content) === '') {
            return ['file' => $file, 'valid' => true];
        }

        \libxml_use_internal_errors(true);

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
                    'message' => \sprintf('There is a mismatch between the file extension [%s] and the [%s] value used in the "target-language" attribute of the file.', $realFileExtension, $targetLanguage),
                ];
            }
        }

        foreach (XliffUtils::validateSchema($document) as $xmlError) {
            $errors[] = [
                'line'    => $xmlError['line'],
                'column'  => $xmlError['column'],
                'message' => $xmlError['message'],
            ];
        }

        return ['file' => $file, 'valid' => 0 === \count($errors), 'messages' => $errors];
    }

    /**
     * {@inheritdoc}
     */
    protected function displayTxt(array $filesInfo, bool $displayCorrectFiles): int
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
}
