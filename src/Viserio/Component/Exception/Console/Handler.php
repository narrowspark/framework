<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Console;

use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class Handler
{

    /**
     * Render an exception to the console.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Throwable                                        $exception
     *
     * @return void
     */
    public function render(OutputInterface $output, Throwable $exception): void
    {
        $exceptionMessage = $exception->getMessage();
        $exceptionName    = get_class($exception);

        $output->writeln(sprintf(
            '<bg=red;options=bold>%s</> : <comment>%s</>',
            $exceptionName,
            $exceptionMessage
        ));
        $output->writeln('');

        $this->renderEditor($output, $exception);
        $output->writeln('<comment>Exception trace:</comment>');
    }

    /**
     * Renders the editor containing the code that was the
     * origin of the exception.
     *
     * @param OutputInterface $output
     * @param \Throwable $exception
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

        $output->writeln('');
    }

    /**
     * Returns the contents of the file for this frame as an
     * array of lines, and optionally as a clamped range of lines.
     *
     * NOTE: lines are 0-indexed
     *
     * @param  int                      $start
     * @param  int                      $length
     *
     * @throws InvalidArgumentException if $length is less than or equal to 0
     *
     * @return string[]|null
     */
    public function getFileLines($start = 0, $length = null)
    {
        if (null !== ($contents = $this->getFileContents())) {
            $lines = explode("\n", $contents);
            // Get a subset of lines from $start to $end
            if ($length !== null) {
                $start  = (int) $start;
                $length = (int) $length;
                if ($start < 0) {
                    $start = 0;
                }

                if ($length <= 0) {
                    throw new InvalidArgumentException(
                        "\$length($length) cannot be lower or equal to 0"
                    );
                }

                $lines = array_slice($lines, $start, $length, true);
            }

            return $lines;
        }
    }
}
