<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Command;

use Invoker\InvokerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Viserio\Component\Console\Application;
use Viserio\Component\Contract\Console\Exception\LogicException;
use Viserio\Component\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contract\Support\Arrayable;

abstract class AbstractCommand extends BaseCommand
{
    use ContainerAwareTrait;

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
     * Indicates whether the command should be shown in the Artisan command list.
     *
     * @var bool
     */
    protected $hidden = false;

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
     * @var null|string
     */
    protected $signature;

    /**
     * The invoker instance.
     *
     * @var \Invoker\InvokerInterface
     */
    protected $invoker;

    /**
     * Create a new console command instance.
     *
     * @throws \Symfony\Component\Console\Exception\LogicException When the command name is empty
     */
    public function __construct()
    {
        // We will go ahead and set the name, description, and parameters on console
        // commands just to make things a little easier.
        if ($this->signature !== null) {
            $this->configureUsingFluentDefinition();
        } else {
            parent::__construct();
        }

        $this->setDescription($this->description);

        $this->setHidden($this->hidden);

        if ($this->signature === null) {
            $this->specifyParameters();
        }
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
     * Get the verbosity level in terms of Symfony's OutputInterface level.
     *
     * @param null|int|string $level
     *
     * @return int
     */
    public function getVerbosity($level = null): int
    {
        if ($level === null) {
            return $this->verbosity;
        }

        if (isset($this->verbosityMap[$level])) {
            return $this->verbosityMap[$level];
        }

        return (int) $level;
    }

    /**
     * Set the verbosity level.
     *
     * @param int|string $level
     *
     * @return void
     */
    public function setVerbosity($level): void
    {
        $this->verbosity = $this->getVerbosity($level);
    }

    /**
     * Set a Invoker instance.
     *
     * @param \Invoker\InvokerInterface $invoker
     *
     * @return void
     */
    public function setInvoker(InvokerInterface $invoker): void
    {
        $this->invoker = $invoker;
    }

    /**
     * Gets the application instance for this command.
     *
     * @return null|\Viserio\Component\Console\Application
     */
    public function getApplication(): ?Application
    {
        return parent::getApplication();
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
        $this->output = new SymfonyStyle(
            $input,
            $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output
        );

        return parent::run($input, $output);
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
        return $this->getApplication()->call($command, $arguments, $this->getOutput());
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
        return $this->getApplication()->call($command, $arguments, new NullOutput());
    }

    /**
     * Get the value of a command argument.
     *
     * @param null|string $key
     *
     * @return null|array|string
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
     * @param null|string $key
     *
     * @return null|array|string
     */
    public function option(?string $key = null)
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
     * Confirm a question with the user.
     *
     * @param string $question
     * @param bool   $default
     *
     * @return bool|string
     */
    public function confirm(string $question, bool $default = false)
    {
        return $this->getOutput()->confirm($question, $default);
    }

    /**
     * Prompt the user for input.
     *
     * @param string      $question
     * @param null|string $default
     *
     * @return null|string
     */
    public function ask(string $question, ?string $default = null): ?string
    {
        return $this->getOutput()->ask($question, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param string $question
     * @param array  $choices
     * @param string $default
     *
     * @return null|string
     */
    public function anticipate(string $question, array $choices, string $default = null): ?string
    {
        return $this->askWithCompletion($question, $choices, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param string      $question
     * @param array       $choices
     * @param null|string $default
     *
     * @return null|string
     */
    public function askWithCompletion(string $question, array $choices, ?string $default = null): ?string
    {
        $question = new Question($question, $default);

        $question->setAutocompleterValues($choices);

        return $this->getOutput()->askQuestion($question);
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

        return $this->getOutput()->askQuestion($question);
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @param string      $question
     * @param array       $choices
     * @param null|string $default
     * @param mixed       $attempts
     * @param bool        $multiple
     *
     * @return null|string
     */
    public function choice(
        string $question,
        array $choices,
        ?string $default = null,
        $attempts        = null,
        bool $multiple   = false
    ): ?string {
        $question = new ChoiceQuestion($question, $choices, $default);

        $question->setMaxAttempts($attempts)->setMultiselect($multiple);

        return $this->getOutput()->askQuestion($question);
    }

    /**
     * Format input to textual table.
     *
     * @param array                                               $headers
     * @param array|\Viserio\Component\Contract\Support\Arrayable $rows
     * @param string                                              $style
     * @param array                                               $columnStyles
     *
     * @return void
     */
    public function table(array $headers, $rows, string $style = 'default', array $columnStyles = []): void
    {
        $table = new Table($this->output);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        $table->setHeaders($headers)->setRows($rows)->setStyle($style);

        foreach ($columnStyles as $columnIndex => $columnStyle) {
            $table->setColumnStyle($columnIndex, $columnStyle);
        }

        $table->render();
    }

    /**
     * Write a string as standard output.
     *
     * @param string          $string
     * @param null|string     $style          The output style of the string
     * @param null|int|string $verbosityLevel
     *
     * @return void
     */
    public function line(string $string, ?string $style = null, $verbosityLevel = null): void
    {
        $styledString = $style ? "<${style}>${string}</${style}>" : $string;
        $this->getOutput()->writeln($styledString, $this->getVerbosity($verbosityLevel));
    }

    /**
     * Write a string as information output.
     *
     * @param string          $string
     * @param null|int|string $verbosityLevel
     *
     * @return void
     */
    public function info(string $string, $verbosityLevel = null): void
    {
        $this->line($string, 'info', $verbosityLevel);
    }

    /**
     * Write a string as comment output.
     *
     * @param string          $string
     * @param null|int|string $verbosityLevel
     *
     * @return void
     */
    public function comment(string $string, $verbosityLevel = null): void
    {
        $this->line($string, 'comment', $verbosityLevel);
    }

    /**
     * Write a string as question output.
     *
     * @param string          $string
     * @param null|int|string $verbosityLevel
     *
     * @return void
     */
    public function question(string $string, $verbosityLevel = null): void
    {
        $this->line($string, 'question', $verbosityLevel);
    }

    /**
     * Write a string as error output.
     *
     * @param string          $string
     * @param null|int|string $verbosityLevel
     *
     * @return void
     */
    public function error(string $string, $verbosityLevel = null): void
    {
        $this->line($string, 'error', $verbosityLevel);
    }

    /**
     * Write a string as warning output.
     *
     * @param string          $string
     * @param null|int|string $verbosityLevel
     *
     * @return void
     */
    public function warn(string $string, $verbosityLevel = null): void
    {
        if (! $this->getOutput()->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');
            $this->getOutput()->getFormatter()->setStyle('warning', $style);
        }

        $this->line($string, 'warning', $verbosityLevel);
    }

    /**
     * Write a string in an alert box.
     *
     * @param string $string
     *
     * @return void
     */
    public function alert(string $string): void
    {
        $length = \mb_strlen(\strip_tags($string)) + 12;

        $this->comment(\str_repeat('*', $length));
        $this->comment('*     ' . $string . '     *');
        $this->comment(\str_repeat('*', $length));

        $this->getOutput()->newLine();
    }

    /**
     * Write a string as task output.
     *
     * @param string   $string
     * @param callable $callable
     *
     * @return void
     */
    public function task($string, callable $callable): void
    {
        $result = $callable() ? '<info>✔</info>' : '<error>fail</error>';

        $this->line($string . ':' . $result);
    }

    /**
     * Get the container instance.
     *
     * @throws \Viserio\Component\Contract\Console\Exception\LogicException
     *
     * @return \Psr\Container\ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        if (! $this->container) {
            throw new LogicException('Container is not set up.');
        }

        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        if ($this->invoker === null) {
            throw new LogicException('Your forgot to call the setInvoker function.');
        }

        /** @var callable $callback */
        $callback = [$this, 'handle'];

        return $this->invoker->call($callback);
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
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \Viserio\Component\Contract\Console\Exception\InvalidCommandExpression
     *
     * @return void
     */
    private function configureUsingFluentDefinition(): void
    {
        $data = ExpressionParser::parse($this->signature);

        parent::__construct($data['name']);

        foreach ($data['arguments'] as $argument) {
            $this->getDefinition()->addArgument($argument);
        }

        foreach ($data['options'] as $option) {
            $this->getDefinition()->addOption($option);
        }
    }

    /**
     * Specify the arguments and options on the command.
     *
     * @return void
     */
    private function specifyParameters(): void
    {
        // We will loop through all of the arguments and options for the command and
        // set them all on the base command instance. This specifies what can get
        // passed into these commands as "parameters" to control the execution.
        foreach ($this->getArguments() as $arguments) {
            $this->addArgument(...$arguments);
        }

        foreach ($this->getOptions() as $options) {
            $this->addOption(...$options);
        }
    }
}
