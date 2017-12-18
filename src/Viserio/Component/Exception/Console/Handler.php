<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Console;

use ErrorException;
use Viserio\Component\Contract\Exception\ConsoleOutput as ConsoleOutputContract;
use Throwable;
use Viserio\Component\Exception\Traits\DetermineErrorLevelTrait;

final class Handler
{
    use DetermineErrorLevelTrait;

    /**
     * The number of frames if no verbosity is specified.
     *
     * @var int
     */
    private const VERBOSITY_NORMAL_FRAMES = 1;

    /**
     * Render an exception to the console.
     *
     * @param \Viserio\Component\Contract\Exception\ConsoleOutput $output
     * @param \Throwable                                        $exception
     *
     * @return void
     */
    public function render(ConsoleOutputContract $output, Throwable $exception): void
    {
        $exceptionMessage = $exception->getMessage();
        $exceptionName    = \get_class($exception);

        $output->writeln('');
        $output->writeln(\sprintf(
            '<bg=red;options=bold>%s</> : <comment>%s</>',
            $exceptionName,
            $exceptionMessage
        ));
        $output->writeln('');

        $this->renderEditor($output, $exception);
        $this->renderTrace($output, $exception);
    }

    /**
     * Renders the editor containing the code that was the
     * origin of the exception.
     *
     * @param \Viserio\Component\Contract\Exception\ConsoleOutput $output
     * @param \Throwable                                        $exception
     *
     * @return void
     */
    private function renderEditor(ConsoleOutputContract $output, Throwable $exception): void
    {
        $output->writeln(\sprintf(
            'at <fg=green>%s</>' . ' : <fg=green>%s</>',
            $exception->getFile(),
            $exception->getLine()
        ));

        $range = self::getFileLines(
            $exception->getFile(),
            $exception->getLine() - 5,
            10
        );

        foreach ($range as $k => $code) {
            $line = $k + 1;
            $code = $exception->getLine() === $line ? \sprintf('<bg=red>%s</>', $code) : $code;
            $output->writeln(\sprintf('%s: %s', $line, $code));
        }

        $output->writeln('');
    }

    /**
     * Renders the trace of the exception.
     *
     * @param \Viserio\Component\Contract\Exception\ConsoleOutput $output
     * @param \Throwable                                        $exception
     *
     * @return void
     */
    private function renderTrace(ConsoleOutputContract $output, Throwable $exception): void
    {
        $output->writeln('<comment>Exception trace:</comment>');
        $output->writeln('');

        $count = 0;

        foreach ($this->getFrames($exception) as $i => $frame) {
            if ($i > static::VERBOSITY_NORMAL_FRAMES &&
                $output->getVerbosity() < ConsoleOutputContract::VERBOSITY_VERBOSE
            ) {
                $output->writeln('');
                $output->writeln(
                    '<info>Please use the argument <fg=red>-v</> to see all trace.</info>'
                );

                break;
            }

            $class    = isset($frame['class']) ? $frame['class'] . '::' : '';
            $function = $frame['function'] ?? '';

            if ($class !== '' && $function !== '') {
                $output->writeln(\sprintf(
                    '<comment><fg=cyan>%s</>%s%s(%s)</comment>',
                    \str_pad((string) ((int) $i + 1), 4, ' '),
                    $class,
                    $function,
                    isset($frame['args']) ? self::formatsArgs($frame['args']) : ''
                ));
            }

            if (isset($frame['file'], $frame['line'])) {
                $output->writeln(\sprintf(
                    '    <fg=green>%s</> : <fg=green>%s</>',
                    $frame['file'],
                    $frame['line']
                ));
            }

            if ($count !== 4) {
                $output->writeln('');
            }

            $count++;
        }
    }

    /**
     * Gets the backtrace from an exception.
     *
     * If xdebug is installed
     *
     * @param \Throwable $exception
     *
     * @return array
     */
    private function getTrace(Throwable $exception): array
    {
        $traces = $exception->getTrace();

        // Get trace from xdebug if enabled, failure exceptions only trace to the shutdown handler by default
        if (! $exception instanceof ErrorException) {
            return $traces;
        }

        if (! self::isLevelFatal($exception->getSeverity())) {
            return $traces;
        }

        if (! \extension_loaded('xdebug') || ! \xdebug_is_enabled()) {
            return [];
        }

        // Use xdebug to get the full stack trace and remove the shutdown handler stack trace
        $stack  = \array_reverse(\xdebug_get_function_stack());
        $trace  = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);
        $traces = \array_diff_key($stack, $trace);

        return $traces;
    }

    /**
     * Returns an iterator for the inspected exception's
     * frames.
     *
     * @param \Throwable $exception
     *
     * @return array
     */
    private function getFrames(Throwable $exception): array
    {
        $frames = $this->getTrace($exception);

        // Fill empty line/file info for call_user_func_array usages (PHP Bug #44428)
        foreach ($frames as $k => $frame) {
            if (empty($frame['file'])) {
                // Default values when file and line are missing
                $file      = '[internal]';
                $line      = 0;
                $nextFrame = ! empty($frames[$k + 1]) ? $frames[$k + 1] : [];

                if ($this->isValidNextFrame($nextFrame)) {
                    $file = $nextFrame['file'];
                    $line = $nextFrame['line'];
                }

                $frames[$k]['file'] = $file;
                $frames[$k]['line'] = $line;
            }

            $frames['function'] = $frame['function'] ?? '';
        }

        // Find latest non-error handling frame index ($i) used to remove error handling frames
        $i = 0;

        foreach ($frames as $k => $frame) {
            if (isset($frame['file'], $frame['line']) &&
                $frame['file'] === $exception->getFile() &&
                $frame['line'] === $exception->getLine()
            ) {
                $i = $k;
            }
        }

        // Remove error handling frames
        if ($i > 0) {
            \array_splice($frames, 0, $i);
        }

        \array_unshift($frames, $this->getFrameFromException($exception));

        // show the last 5 frames
        return \array_slice($frames, 0, 5);
    }

    /**
     * Given an exception, generates an array in the format
     * generated by Exception::getTrace().
     *
     * @param \Throwable $exception
     *
     * @return array
     */
    private function getFrameFromException(Throwable $exception): array
    {
        return [
            'file'     => $exception->getFile(),
            'line'     => $exception->getLine(),
            'class'    => \get_class($exception),
            'function' => '__construct',
            'args'     => [
                $exception->getMessage(),
            ],
        ];
    }

    /**
     * Determine if the frame can be used to fill in previous frame's missing info
     * happens for call_user_func and call_user_func_array usages (PHP Bug #44428).
     *
     * @param array $frame
     *
     * @return bool
     */
    private function isValidNextFrame(array $frame): bool
    {
        if (empty($frame['file'])) {
            return false;
        }

        if (empty($frame['line'])) {
            return false;
        }

        if (empty($frame['function']) || \mb_stripos($frame['function'], 'call_user_func') === false) {
            return false;
        }

        return true;
    }

    /**
     * Format the given function args to a string.
     *
     * @param array $arguments
     * @param bool  $recursive
     *
     * @return string
     */
    private static function formatsArgs(array $arguments, bool $recursive = true): string
    {
        $result = [];

        foreach ($arguments as $argument) {
            switch (true) {
                case \is_string($argument):
                    $result[] = '"' . $argument . '"';

                    break;
                case \is_array($argument):
                    $associative = \array_keys($argument) !== \range(0, \count($argument) - 1);

                    if ($recursive && $associative && \count($argument) <= 5) {
                        $result[] = '[' . self::formatsArgs($argument, false) . ']';
                    }

                    break;
                case \is_object($argument):
                    $class    = \get_class($argument);
                    $result[] = "Object($class)";

                    break;
            }
        }

        return implode(', ', $result);
    }

    /**
     * Returns the contents of the file for this frame as an
     * array of lines, and optionally as a clamped range of lines.
     *
     * @param string $filePath
     * @param int    $start
     * @param int    $length
     *
     * @return null|string[]
     */
    private static function getFileLines(string $filePath, int $start, int $length): ?array
    {
        if (($contents = self::getFileContents($filePath)) !== null) {
            $lines = \explode("\n", $contents);

            if ($start < 0) {
                $start = 0;
            }

            return \array_slice($lines, $start, $length, true);
        }
    }

    /**
     * Returns the full contents of the file for this frame,
     * if it's known.
     *
     * @param string $filePath
     *
     * @return null|string
     */
    private static function getFileContents(string $filePath): ?string
    {
        // Leave the stage early when 'Unknown' is passed
        // this would otherwise raise an exception when
        // open_basedir is enabled.
        if ($filePath === 'Unknown') {
            return null;
        }

        // Return null if the file doesn't actually exist.
        if (! \is_file($filePath)) {
            return null;
        }

        return \file_get_contents($filePath);
    }
}
