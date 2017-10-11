<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Console;

use InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class Handler
{
    /**
     * The number of frames if no verbosity is specified.
     *
     * @var int
     */
    private const VERBOSITY_NORMAL_FRAMES = 1;

    /**
     * @var string
     */
    private $fileContentsCache;

    /**
     * Render an exception to the console.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Throwable                                        $exception
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function render(OutputInterface $output, Throwable $exception): void
    {
        $exceptionMessage = $exception->getMessage();
        $exceptionName    = get_class($exception);

        try {
            $output->writeln(sprintf(
                '<bg=red;options=bold>%s</> : <comment>%s</>',
                $exceptionName,
                $exceptionMessage
            ));
            $output->writeln('');

            $this->renderEditor($output, $exception);
            $this->renderTrace($output, $exception);
        } catch (\Throwable $exception) {
            $output->writeln($exception->getMessage());
        }
    }

    /**
     * Returns the contents of the file for this frame as an
     * array of lines, and optionally as a clamped range of lines.
     *
     * NOTE: lines are 0-indexed
     *
     * @param string $filePath
     * @param int    $start
     * @param int    $length
     *
     * @throws \InvalidArgumentException if $length is less than or equal to 0
     *
     * @return string[]|null
     */
    private function getFileLines(string $filePath, int $start = 0, int $length = null): ?array
    {
        if (($contents = $this->getFileContents($filePath)) !== null) {
            $lines = explode("\n", $contents);
            // Get a subset of lines from $start to $end
            if ($length !== null) {
                if ($start < 0) {
                    $start = 0;
                }

                if ($length <= 0) {
                    throw new InvalidArgumentException(sprintf(
                        '$length(%s) cannot be lower or equal to 0',
                        $length
                    ));
                }

                $lines = array_slice($lines, $start, $length, true);
            }

            return $lines;
        }
    }

    /**
     * Returns the full contents of the file for this frame,
     * if it's known.
     *
     * @var string
     *
     * @param mixed $filePath
     *
     * @return string|null
     */
    private function getFileContents($filePath): ?string
    {
        if ($this->fileContentsCache === null) {
            // Leave the stage early when 'Unknown' is passed
            // this would otherwise raise an exception when
            // open_basedir is enabled.
            if ($filePath === 'Unknown') {
                return null;
            }

            // Return null if the file doesn't actually exist.
            if (! is_file($filePath)) {
                return null;
            }

            $this->fileContentsCache = file_get_contents($filePath);
        }

        return $this->fileContentsCache;
    }

    /**
     * Renders the editor containing the code that was the
     * origin of the exception.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Throwable                                        $exception
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    private function renderEditor(OutputInterface $output, Throwable $exception): void
    {
        $output->writeln(sprintf(
            ' at <fg=green>%s</>' . ': <fg=green>%s</>',
            $exception->getFile(),
            $exception->getLine()
        ));

        $range = $this->getFileLines(
            $exception->getFile(),
            $exception->getLine() - 5,
            10
        );

        foreach ($range as $k => $code) {
            $line = $k + 1;
            $code = $exception->getLine() === $line ? sprintf('<bg=red>%s</>', $code) : $code;
            $output->writeln(sprintf('%s: %s', $line, $code));
        }

        $output->writeln('');
    }

    /**
     * Renders the trace of the exception.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Throwable                                        $exception
     *
     * @return \void
     */
    private function renderTrace(OutputInterface $output, Throwable $exception): void
    {
        $output->writeln('<comment>Exception trace:</comment>');

        foreach ($exception->getTrace() as $i => $frame) {
            if ($i > static::VERBOSITY_NORMAL_FRAMES &&
                $output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE
            ) {
                $output->writeln('');
                $output->writeln(
                    '<info>Please use the argument <fg=red>-v</> to see all trace.</info>'
                );
                break;
            }

            $output->writeln(sprintf(
                '<comment><fg=cyan>%s</>%s%s(%s)</comment>',
                str_pad((string) ($i + 1), 4, ' '),
                ($frame['class'] ?? '') . '::',
                $frame['function'],
                $this->formatsArgs($frame['args'])
            ));

            $output->writeln('');

            $output->writeln(sprintf(
                '    <fg=green>%s</> : <fg=green>%s</>',
                $frame['file'],
                $frame['line']
            ));
        }
    }

    /**
     * Format the given function args to a string.
     *
     * @param array $arguments
     * @param bool  $recursive
     *
     * @return string
     */
    private function formatsArgs(array $arguments, bool $recursive = true): string
    {
        $result = [];

        foreach ($arguments as $argument) {
            switch (true) {
                case is_string($argument):
                    $result[] = '"' . $argument . '"';
                    break;
                case is_array($argument):
                    $associative = array_keys($argument) !== range(0, count($argument) - 1);

                    if ($recursive && $associative && count($argument) <= 5) {
                        $result[] = '[' . $this->formatsArgs($argument, false) . ']';
                    }

                    break;
                case is_object($argument):
                    $class    = get_class($argument);
                    $result[] = "Object($class)";
                    break;
            }
        }

        return implode(', ', $result);
    }
}
