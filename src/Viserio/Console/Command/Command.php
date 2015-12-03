<?php
namespace Viserio\Console\Command;

use Viserio\Contracts\Support\Arrayable;
use Viserio\Container\ContainerAwareTrait;
use Viserio\Console\Style\NarrowsparkStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Viserio\Console\Style\NarrowsparkStyle;
use Viserio\Container\ContainerAwareTrait;

abstract class Command extends BaseCommand
{
    use ContainerAwareTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description;

    /**
     * The console command input.
     *
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * The console command output.
     *
     * @var \Viserio\Console\Style\NarrowsparkStyle
     */
    protected $output;

    /**
     * The mapping between human readable verbosity levels and Symfony's
     * OutputInterface.
     *
     * @var array
     */
    protected $verbosity = [
        'v'     => OutputInterface::VERBOSITY_VERBOSE,
        'vv'    => OutputInterface::VERBOSITY_VERY_VERBOSE,
        'vvv'   => OutputInterface::VERBOSITY_DEBUG,
        'quiet' => OutputInterface::VERBOSITY_QUIET,
    ];

    /**
     * Create a new console command instance.
     */
    public function __construct()
    {
        parent::__construct($this->name);

        $this->setDescription($this->description);
    }

    /**
     * Run the console command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = new NarrowsparkStyle($input, $output);

        return parent::run($input, $output);
    }

    /**
     * Get the output implementation.
     *
     * @return \Viserio\Console\Style\NarrowsparkStyle
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Check if verbosity level is in bounds.
     *
     * @param  string|int $level
     *
     * @return bool
     */
    public function getVerbosityLevel($level)
    {
        if (isset($this->verbosity[$level])) {
            $level = $this->verbosity[$level];
        } elseif (!is_int($level)) {
            $level = OutputInterface::VERBOSITY_NORMAL;
        }

        return $this->getOutput()->getVerbosity() >= $level;
    }

    /**
     * Call another console command.
     *
     * @param  string  $command
     * @param  array   $arguments
     * @return int
     */
    public function call($command, array $arguments = [])
    {
        $instance = $this->getApplication()->find($command);
        $arguments['command'] = $command;

        return $instance->run(new ArrayInput($arguments), $this->output);
    }

    /**
     * Call another console command silently.
     *
     * @param string $command
     * @param array  $arguments
     *
     * @return int
     */
    public function callSilent($command, array $arguments = [])
    {
        $instance = $this->getApplication()->find($command);
        $arguments['command'] = $command;

        return $instance->run(new ArrayInput($arguments), new NullOutput);
    }

    /**
     * Get the value of a command argument.
     *
     * @param string|null $key
     *
     * @return string|array
     */
    public function argument($key = null)
    {
        if ($key === null) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * Get the value of a command option.
     *
     * @param string|null $key
     *
     * @return string|array
     */
    public function option($key = null)
    {
        if ($key === null) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    /**
     * Confirm a question with the user.
     *
     * @param string $question
     * @param bool   $default
     *
     * @return string
     */
    public function confirm($question, $default = false)
    {
        return $this->output->confirm($question, $default);
    }

    /**
     * Prompt the user for input.
     *
     * @param string      $question
     * @param string|null $default
     *
     * @return string
     */
    public function ask($question, $default = null)
    {
        return $this->output->ask($question, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param string $question
     * @param array  $choices
     * @param string $default
     *
     * @return string
     */
    public function anticipate($question, array $choices, $default = null)
    {
        return $this->askWithCompletion($question, $choices, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param string      $question
     * @param array       $choices
     * @param string|null $default
     *
     * @return string
     */
    public function askWithCompletion($question, array $choices, $default = null)
    {
        $question = new Question($question, $default);

        $question->setAutocompleterValues($choices);

        return $this->output->askQuestion($question);
    }

    /**
     * Prompt the user for input but hide the answer from the console.
     *
     * @param string $question
     * @param bool   $fallback
     *
     * @return string
     */
    public function secret($question, $fallback = true)
    {
        $question = new Question($question);

        $question->setHidden(true)->setHiddenFallback($fallback);

        return $this->output->askQuestion($question);
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @param string      $question
     * @param array       $choices
     * @param string|null $default
     * @param mixed       $attempts
     * @param bool|null   $multiple
     *
     * @return string
     */
    public function choice($question, array $choices, $default = null, $attempts = null, $multiple = null)
    {
        $question = new ChoiceQuestion($question, $choices, $default);

        $question->setMaxAttempts($attempts)->setMultiselect($multiple);

        return $this->output->askQuestion($question);
    }

    /**
     * Format input to textual table.
     *
     * @param array                                      $headers
     * @param array|\Viserio\Contracts\Support\Arrayable $rows
     * @param string                                     $style
     */
    public function table(array $headers, $rows, $style = 'default')
    {
        $table = new Table($this->output);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        $table->setHeaders($headers)->setRows($rows)->setStyle($style)->render();
    }

    /**
     * Write a string as information output.
     *
     * @param string     $string
     * @param int|string $verbosityLevel
     */
    public function info($string, $verbosityLevel = OutputInterface::VERBOSITY_NORMAL)
    {
        $this->line($string, 'info', $verbosityLevel);
    }

    /**
     * Write a string as standard output.
     *
     * @param string      $string
     * @param string|null $style The output style of the string
     * @param int|string  $verbosityLevel
     */
    public function line($string, $newline = true, $style = null, $verbosityLevel = OutputInterface::VERBOSITY_NORMAL)
    {
        if ($this->getVerbosityLevel($verbosityLevel)) {
            $this->output->writeln($style ? "<$style>$string</$style>" : $string);
        }
    }

    /**
     * Write a string as comment output.
     *
     * @param string     $string
     * @param int|string $verbosityLevel
     */
    public function comment($string, $verbosityLevel = OutputInterface::VERBOSITY_NORMAL)
    {
        $this->line($string, 'comment', $verbosityLevel);
    }

    /**
     * Write a string as question output.
     *
     * @param string     $string
     * @param int|string $verbosityLevel
     */
    public function question($string, $verbosityLevel = OutputInterface::VERBOSITY_NORMAL)
    {
        $this->line($string, 'question', $verbosityLevel);
    }

    /**
     * Write a string as error output.
     *
     * @param string $string
     * @param int|string $verbosityLevel
     */
    public function error($string, $verbosityLevel = OutputInterface::VERBOSITY_NORMAL)
    {
        $this->line($string, 'error', $verbosityLevel);
    }

    /**
     * Write a string as warning output.
     *
     * @param string     $string
     * @param int|string $verbosityLevel
     *
     * @return void
     */
    public function warn($string, $verbosityLevel = OutputInterface::VERBOSITY_NORMAL)
    {
        if (!$this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');
            $this->output->getFormatter()->setStyle('warning', $style);
        }

        $this->line($string, 'warning', $verbosityLevel);
    }
}
