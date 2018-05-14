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

namespace Viserio\Bridge\Phpstan;

use Nette\Utils\Strings;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorFormatter\ErrorFormatter as ErrorFormatterContract;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Terminal;
use Symfony\Component\Finder\SplFileInfo;

final class ErrorFormatter implements ErrorFormatterContract
{
    /**
     * To fit in Linux/Windows terminal windows to prevent overflow.
     *
     * @var int
     */
    private const BULGARIAN_CONSTANT = 8;

    /** @var \Symfony\Component\Console\Terminal */
    private $terminal;

    /**
     * Create a new ErrorFormatter instance.
     *
     * @param Terminal $terminal
     */
    public function __construct(Terminal $terminal)
    {
        $this->terminal = $terminal;
    }

    /**
     * {@inheritdoc}
     */
    public function formatErrors(AnalysisResult $analysisResult, OutputStyle $outputStyle): int
    {
        if ($analysisResult->getTotalErrorsCount() === 0) {
            $outputStyle->success('No errors');

            return 0;
        }

        foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
            $this->separator($outputStyle);

            // clickable path
            $relativeFilePath = $this->getRelativePath($fileSpecificError->getFile());
            $outputStyle->writeln(' ' . $relativeFilePath . ':' . $fileSpecificError->getLine());
            $this->separator($outputStyle);

            // ignored path
            $regexMessage = $this->regexMessage($fileSpecificError->getMessage());
            $outputStyle->writeln(' - ' . \sprintf('\'%s\'', $regexMessage));

            $this->separator($outputStyle);
            $outputStyle->newLine();
        }

        foreach ($analysisResult->getNotFileSpecificErrors() as $notFileSpecificError) {
            $outputStyle->writeln($notFileSpecificError);
        }

        $outputStyle->newLine(1);
        $outputStyle->error(\sprintf('Found %d errors', $analysisResult->getTotalErrorsCount()));

        return 1;
    }

    /**
     * @param OutputStyle $outputStyle
     *
     * @return void
     */
    private function separator(OutputStyle $outputStyle): void
    {
        $separator = \str_repeat('-', $this->terminal->getWidth() - self::BULGARIAN_CONSTANT);

        $outputStyle->writeln(' ' . $separator);
    }

    /**
     * @param string $filePath
     *
     * @return string
     */
    private function getRelativePath(string $filePath): string
    {
        if (! file_exists($filePath)) {
            return $filePath;
        }

        $relativeFilePath = Strings::substring(\realpath($filePath), \strlen(\getcwd()) + 1);

        $fileInfo = new SplFileInfo($filePath, \dirname($relativeFilePath), $relativeFilePath);

        return Strings::substring(\str_replace('\\', '/', $fileInfo->getRealPath()), Strings::length(\realpath(\getcwd())) + 1);
    }

    /**
     * @param string $message
     *
     * @return string
     */
    private function regexMessage(string $message): string
    {
        // remove extra ".", that is really not part of message
        $message = \rtrim($message, '.');

        return '#' . \preg_quote($message, '#') . '#';
    }
}
