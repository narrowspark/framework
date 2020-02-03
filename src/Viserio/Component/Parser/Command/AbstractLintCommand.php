<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Parser\Command;

use FilesystemIterator;
use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Contract\Parser\Exception\InvalidArgumentException;
use Viserio\Contract\Parser\Exception\RuntimeException;
use const STDIN;

abstract class AbstractLintCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Contract\Parser\Exception\RuntimeException
     */
    public function handle(): int
    {
        /** @var null|string $filename */
        $filename = $this->argument('filename');
        /** @var string $format */
        $format = $this->option('format');

        $displayCorrectFiles = $this->getOutput()->isVerbose();

        if ($filename === null || $filename === '') {
            if (null === $stdin = $this->getStdin()) {
                throw new RuntimeException('Please provide a filename or pipe file content to STDIN.');
            }

            return $this->display([$this->validate($stdin)], $format, $displayCorrectFiles);
        }

        if (! \is_readable($filename)) {
            throw new RuntimeException(\sprintf('File or directory [%s] is not readable.', $filename));
        }

        $filesInfo = [];

        /** @var string $file */
        foreach ($this->getFiles($filename) as $file) {
            $filesInfo[] = $this->validate((string) \file_get_contents($file), $file);
        }

        return $this->display($filesInfo, $format, $displayCorrectFiles);
    }

    /**
     * Get display type from format.
     *
     * @param array<int, array<string, null|array<int, array<string, mixed>>|bool|string>> $files
     * @param string                                                                       $format
     * @param bool                                                                         $displayCorrectFiles
     *
     * @throws \Viserio\Contract\Parser\Exception\InvalidArgumentException
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
                throw new InvalidArgumentException(\sprintf('The format [%s] is not supported.', $format));
        }
    }

    /**
     * Display errors in txt format.
     *
     * @param array<int, array<string, null|array<int, array<string, mixed>>|bool|string>> $filesInfo
     * @param bool                                                                         $displayCorrectFiles
     *
     * @return int
     */
    abstract protected function displayTxt(array $filesInfo, bool $displayCorrectFiles): int;

    /**
     * Display errors in json format.
     *
     * @param array<int, array<string, null|array<int, array<string, mixed>>|bool|string>> $filesInfo
     *
     * @return int
     */
    protected function displayJson(array $filesInfo): int
    {
        $errors = 0;

        \array_walk($filesInfo, static function (array &$v) use (&$errors): void {
            if (! \is_string($v['file'])) {
                $v['file'] = $v['file']->getPathname();
            }

            if (! $v['valid']) {
                $errors++;
            }
        });

        $this->getOutput()->writeln(\json_encode($filesInfo, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR));

        return \min($errors, 1);
    }

    /**
     * Validate file content.
     *
     * @param string      $content
     * @param null|string $file
     *
     * @return array<string, mixed>
     */
    abstract protected function validate(string $content, ?string $file = null): array;

    /**
     * Get a generator of files.
     *
     * @param string $fileOrDirectory
     *
     * @return Generator<string>
     */
    protected function getFiles(string $fileOrDirectory): Generator
    {
        if (\is_file($fileOrDirectory)) {
            yield $fileOrDirectory;

            return;
        }

        /** @var SplFileInfo $file */
        foreach (self::getDirectoryIterator($fileOrDirectory) as $file) {
            if (! \in_array($file->getExtension(), ['xlf', 'xliff'], true)) {
                continue;
            }

            yield (string) $file;
        }
    }

    /**
     * Get content from stdin.
     *
     * @return null|string
     */
    protected function getStdin(): ?string
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
     * Get item from dirs.
     *
     * @param string $directory
     *
     * @return RecursiveIteratorIterator<string, SplFileInfo>
     */
    protected static function getDirectoryIterator(string $directory): RecursiveIteratorIterator
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS | FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
    }
}
