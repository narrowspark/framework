<?php
namespace Brainwave\Console\Command;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */
use Brainwave\Container\ContainerAwareTrait;
use Brainwave\Console\Style\NarrowsparkStyle;
use Brainwave\Container\ContainerAwareTrait;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Command.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
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
     * @var \Brainwave\Console\Style\NarrowsparkStyle
     */
    protected $output;

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
     * @return \Brainwave\Console\Style\NarrowsparkStyle
     */
    public function getOutput()
    {
        return $this->output;
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
     * @param array $headers
     * @param array $rows
     */
    public function table(array $headers, array $rows)
    {
        $this->output->table($headers, $rows);
    }

    /**
     * Write a string as information output.
     *
     * @param string $string
     * @param bool   $newline
     */
    public function info($string, $newline = true)
    {
        if ($newline) {
            $this->output->writeln(sprintf('<info>%s</info>',$string));
        } else {
            $this->output->write(sprintf('<info>%s</info>',$string));
        }
    }

    /**
     * Write a string as standard output.
     *
     * @param string $string
     * @param bool   $newline
     */
    public function line($string, $newline = true)
    {
        if ($newline) {
            $this->output->writeln($string);
        } else {
            $this->output->write($string);
        }
    }

    /**
     * Write a string as comment output.
     *
     * @param string $string
     */
    public function comment($string)
    {
        $this->output->note($string);
    }

    /**
     * Write a string as question output.
     *
     * @param string $string
     */
    public function question($string)
    {
        $this->output->writeln(sprintf('<question></question>', $string));
    }

    /**
     * Write a string as error output.
     *
     * @param string $string
     */
    public function error($string)
    {
        $this->output->error($string);
    }
}
