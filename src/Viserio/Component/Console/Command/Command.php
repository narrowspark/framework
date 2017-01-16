<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Command;

use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\Support\Arrayable;
use Viserio\Component\Support\Invoker;

abstract class Command extends BaseCommand implements CompletionAwareInterface
{
    use ContainerAwareTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name;

    /**
     * The console command input.
     *
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * The console command output.
     *
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    protected $output;

    /**
     * The mapping between human readable verbosity levels and Symfony's
     * OutputInterface.
     *
     * @var array
     */
    protected $verbosityMap = [
        'v'      => OutputInterface::VERBOSITY_VERBOSE,
        'vv'     => OutputInterface::VERBOSITY_VERY_VERBOSE,
        'vvv'    => OutputInterface::VERBOSITY_DEBUG,
        'quiet'  => OutputInterface::VERBOSITY_QUIET,
        'normal' => OutputInterface::VERBOSITY_NORMAL,
    ];

    /**
     * The default verbosity of output commands.
     *
     * @var int
     */
    protected $verbosity = OutputInterface::VERBOSITY_NORMAL;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature;

    /**
     * Invoker instance.
     *
     * @var \Viserio\Component\Support\Invoker
     */
    protected $invoker;

    /**
     * Create a new console command instance.
     */
    public function __construct()
    {
        // We will go ahead and set the name, description, and parameters on console
        // commands just to make things a little easier on the developer. This is
        // so they don't have to all be manually specified in the constructors.
        if (isset($this->signature)) {
            $this->configureUsingFluentDefinition();
        } else {
            parent::__construct($this->name);
        }

        $this->setDescription($this->description);

        if (! isset($this->signature)) {
            $this->specifyParameters();
        }
    }

    /**
     * Run the console command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->input  = $input;
        $this->output = new SymfonyStyle($input, $output);

        return parent::run($input, $output);
    }

    /**
     * Get the output implementation.
     *
     * @return \Symfony\Component\Console\Style\SymfonyStyle
     *
     * @codeCoverageIgnore
     */
    public function getOutput(): SymfonyStyle
    {
        return $this->output;
    }

    /**
     * Set the verbosity level.
     *
     * @param string|int $level
     */
    public function setVerbosity($level)
    {
        $this->verbosity = $this->getVerbosity($level);
    }

    /**
     * Get the verbosity level in terms of Symfony's OutputInterface level.
     *
     * @param null|string|int $level
     *
     * @return int
     */
    public function getVerbosity($level = null): int
    {
        if (isset($this->verbosityMap[$level])) {
            return $this->verbosityMap[$level];
        } elseif (! is_int($level)) {
            return $this->verbosity;
        }

        return $level;
    }

    /**
     * Call another console command.
     *
     * @param string $command
     * @param array  $arguments
     *
     * @return int
     */
    public function call(string $command, array $arguments = []): int
    {
        $instance             = $this->getApplication()->find($command);
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
    public function callSilent(string $command, array $arguments = []): int
    {
        $instance             = $this->getApplication()->find($command);
        $arguments['command'] = $command;

        return $instance->run(new ArrayInput($arguments), new NullOutput());
    }

    /**
     * Get the value of a command argument.
     *
     * @param string|null $key
     *
     * @return string|array
     */
    public function argument(?string $key = null)
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
     * Check if a command option is set.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasOption(string $key): bool
    {
        return $this->input->hasParameterOption('--' . $key);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function completeArgumentValues($argumentName, CompletionContext $context)
    {
    }

    /**
     * Confirm a question with the user.
     *
     * @param string $question
     * @param bool   $default
     *
     * @return string|bool
     */
    public function confirm(string $question, bool $default = false)
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
    public function ask(string $question, ?string $default = null): string
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
    public function anticipate(string $question, array $choices, string $default = null): string
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
    public function askWithCompletion(string $question, array $choices, ?string $default = null): string
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
    public function secret(string $question, bool $fallback = true): string
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
     * @param bool        $multiple
     *
     * @return string
     */
    public function choice(
        string $question,
        array $choices,
        ?string $default = null,
        $attempts = null,
        bool $multiple = false
    ): string {
        $question = new ChoiceQuestion($question, $choices, $default);

        $question->setMaxAttempts($attempts)->setMultiselect($multiple);

        return $this->output->askQuestion($question);
    }

    /**
     * Format input to textual table.
     *
     * @param array                                      $headers
     * @param array|\Viserio\Component\Contracts\Support\Arrayable $rows
     * @param string                                     $style
     */
    public function table(array $headers, $rows, string $style = 'default')
    {
        $table = new Table($this->output);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        $table->setHeaders($headers)->setRows($rows)->setStyle($style)->render();
    }

    /**
     * Write a string as standard output.
     *
     * @param string          $string
     * @param string|null     $style          The output style of the string
     * @param null|int|string $verbosityLevel
     */
    public function line(string $string, ?string $style = null, $verbosityLevel = null)
    {
        $styledString = $style ? "<$style>$string</$style>" : $string;
        $this->output->writeln($styledString, $this->getVerbosity($verbosityLevel));
    }

    /**
     * Write a string as information output.
     *
     * @param string          $string
     * @param null|int|string $verbosityLevel
     */
    public function info(string $string, $verbosityLevel = null)
    {
        $this->line($string, 'info', $verbosityLevel);
    }

    /**
     * Write a string as comment output.
     *
     * @param string          $string
     * @param null|int|string $verbosityLevel
     */
    public function comment(string $string, $verbosityLevel = null)
    {
        $this->line($string, 'comment', $verbosityLevel);
    }

    /**
     * Write a string as question output.
     *
     * @param string          $string
     * @param null|int|string $verbosityLevel
     */
    public function question(string $string, $verbosityLevel = null)
    {
        $this->line($string, 'question', $verbosityLevel);
    }

    /**
     * Write a string as error output.
     *
     * @param string          $string
     * @param null|int|string $verbosityLevel
     */
    public function error(string $string, $verbosityLevel = null)
    {
        $this->line($string, 'error', $verbosityLevel);
    }

    /**
     * Write a string as warning output.
     *
     * @param string          $string
     * @param null|int|string $verbosityLevel
     */
    public function warn(string $string, $verbosityLevel = null)
    {
        if (! $this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');
            $this->output->getFormatter()->setStyle('warning', $style);
        }

        $this->line($string, 'warning', $verbosityLevel);
    }

    /**
     * Set invoker.
     *
     * @param \Viserio\Component\Support\Invoker
     * @param Invoker $invoker
     */
    public function setInvoker(Invoker $invoker)
    {
        $this->invoker = $invoker;
    }

    /**
     * Execute the console command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->getInvoker()->call([$this, 'handle']);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [];
    }

    /**
     * Configure the console command using a fluent definition.
     */
    protected function configureUsingFluentDefinition()
    {
        $arr = (new ExpressionParser())->parse($this->signature);

        parent::__construct($arr['name']);

        foreach ($arr['arguments'] as $argument) {
            $this->getDefinition()->addArgument($argument);
        }

        foreach ($arr['options'] as $option) {
            $this->getDefinition()->addOption($option);
        }
    }

    /**
     * Specify the arguments and options on the command.
     */
    protected function specifyParameters()
    {
        // We will loop through all of the arguments and options for the command and
        // set them all on the base command instance. This specifies what can get
        // passed into these commands as "parameters" to control the execution.
        foreach ($this->getArguments() as $arguments) {
            call_user_func_array([$this, 'addArgument'], $arguments);
        }

        foreach ($this->getOptions() as $options) {
            call_user_func_array([$this, 'addOption'], $options);
        }
    }

    /**
     * Get configured invoker.
     *
     * @return \Viserio\Component\Support\Invoker
     */
    protected function getInvoker(): Invoker
    {
        return $this->invoker;
    }
}
