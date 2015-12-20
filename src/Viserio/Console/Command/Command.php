<?php
namespace Viserio\Console\Command;

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
use Viserio\Console\Style\NarrowsparkStyle;
use Viserio\Contracts\Support\Arrayable;
use Viserio\Support\Invoker;
use Viserio\Support\Traits\ContainerAwareTrait;

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
     * @var \Viserio\Console\Style\NarrowsparkStyle
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
     * @var \Viserio\Support\Invoker
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

        if (!isset($this->signature)) {
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
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = new NarrowsparkStyle($input, $output);

        return parent::run($input, $output);
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
        $method = method_exists($this, 'handle') ? 'handle' : 'fire';

        return $this->getInvoker()->call([$this, $method]);
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
    public function getVerbosity($level = null)
    {
        if (isset($this->verbosityMap[$level])) {
            return $this->verbosityMap[$level];
        } elseif (!is_int($level)) {
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

    public function completeOptionValues($optionName, CompletionContext $context)
    {
        //
    }

    public function completeArgumentValues($argumentName, CompletionContext $context)
    {
        //
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
     * Write a string as standard output.
     *
     * @param string           $string
     * @param string|null      $style The output style of the string
     * @param null|int|string  $verbosityLevel
     */
    public function line($string, $style = null, $verbosityLevel = null)
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
    public function info($string, $verbosityLevel = null)
    {
        $this->line($string, 'info', $verbosityLevel);
    }

    /**
     * Write a string as comment output.
     *
     * @param string          $string
     * @param null|int|string $verbosityLevel
     */
    public function comment($string, $verbosityLevel = null)
    {
        $this->line($string, 'comment', $verbosityLevel);
    }

    /**
     * Write a string as question output.
     *
     * @param string          $string
     * @param null|int|string $verbosityLevel
     */
    public function question($string, $verbosityLevel = null)
    {
        $this->line($string, 'question', $verbosityLevel);
    }

    /**
     * Write a string as error output.
     *
     * @param string          $string
     * @param null|int|string $verbosityLevel
     */
    public function error($string, $verbosityLevel = null)
    {
        $this->line($string, 'error', $verbosityLevel);
    }

    /**
     * Write a string as warning output.
     *
     * @param string          $string
     * @param null|int|string $verbosityLevel
     */
    public function warn($string, $verbosityLevel = null)
    {
        if (!$this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');
            $this->output->getFormatter()->setStyle('warning', $style);
        }

        $this->line($string, 'warning', $verbosityLevel);
    }

    /**
     * Set invoker.
     *
     * @param \Viserio\Support\Invoker
     */
    public function setInvoker(Invoker $invoker)
    {
        $this->invoker = $invoker;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }

    /**
     * Configure the console command using a fluent definition.
     */
    protected function configureUsingFluentDefinition()
    {
        list($name, $arguments, $options) = (new ExpressionParser())->parse($this->signature);

        parent::__construct($name);

        foreach ($arguments as $argument) {
            $this->getDefinition()->addArgument($argument);
        }
        foreach ($options as $option) {
            $this->getDefinition()->addOption($option);
        }
    }

    /**
     * Specify the arguments and options on the command.
     *
     * @return void
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
     * @return \Viserio\Support\Invoker
     */
    protected function getInvoker()
    {
        return $this->invoker;
    }
}
