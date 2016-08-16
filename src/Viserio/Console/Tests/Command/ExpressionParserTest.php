<?php
declare(strict_types=1);
namespace Viserio\Console\Tests\Command;

use Viserio\Console\Command\ExpressionParser;
use Viserio\Console\Input\{
    InputArgument,
    InputOption
};

class ExpressionParserTest extends \PHPUnit_Framework_TestCase
{
    public function testItParsesCommandNames()
    {
        $this->assertParsesTo('greet', [
            'name' => 'greet',
            'arguments' => [],
            'options' => [],
        ]);
    }

    public function testItParsesCommandNamesContainingNamespaces()
    {
        $this->assertParsesTo('demo:greet', [
            'name' => 'demo:greet',
            'arguments' => [],
            'options' => [],
        ]);
    }

    public function testItParsesMandatoryArguments()
    {
        $this->assertParsesTo('greet firstname lastname', [
            'name' => 'greet',
            'arguments' => [
                new InputArgument('firstname', InputArgument::REQUIRED),
                new InputArgument('lastname', InputArgument::REQUIRED),
            ],
            'options' => [],
        ]);
    }

    public function testItParsesOptionalArguments()
    {
        $this->assertParsesTo('greet [firstname] [lastname]', [
            'name' => 'greet',
            'arguments' => [
                new InputArgument('firstname', InputArgument::OPTIONAL),
                new InputArgument('lastname', InputArgument::OPTIONAL),
            ],
            'options' => [],
        ]);
    }

    public function testItParsesArrayArguments()
    {
        $this->assertParsesTo('greet [names]*', [
            'name' => 'greet',
            'arguments' => [
                new InputArgument('names', InputArgument::IS_ARRAY),
            ],
            'options' => [],
        ]);
    }

    public function testItParsesArrayArgumentsWithAtLeastOneValue()
    {
        $this->assertParsesTo('greet names*', [
            'name' => 'greet',
            'arguments' => [
                new InputArgument('names', InputArgument::IS_ARRAY | InputArgument::REQUIRED),
            ],
            'options' => [],
        ]);
    }

    public function testItParsesOptions()
    {
        $this->assertParsesTo('greet [--yell]', [
            'name' => 'greet',
            'arguments' => [],
            'options' => [
                new InputOption('yell', null, InputOption::VALUE_NONE),
            ],
        ]);
    }

    public function testItParsesOptionsWithMandatoryValues()
    {
        $this->assertParsesTo('greet [--iterations=]', [
            'name' => 'greet',
            'arguments' => [],
            'options' => [
                new InputOption('iterations', null, InputOption::VALUE_REQUIRED),
            ],
        ]);
    }

    public function testItParsesOptionsWithMultipleValues()
    {
        $this->assertParsesTo('greet [--name=]*', [
            'name' => 'greet',
            'arguments' => [],
            'options' => [
                new InputOption('name', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
            ],
        ]);
    }

    public function testItParsesOptionsWithShortcuts()
    {
        $this->assertParsesTo('greet [-y|--yell] [-it|--iterations=] [-n|--name=]*', [
            'name' => 'greet',
            'arguments' => [],
            'options' => [
                new InputOption('yell', 'y', InputOption::VALUE_NONE),
                new InputOption('iterations', 'it', InputOption::VALUE_REQUIRED),
                new InputOption('name', 'n', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
            ],
        ]);
    }

    /**
     * @expectedException \Viserio\Contracts\Console\Exceptions\InvalidCommandExpression
     * @expectedExceptionMessage An option must be enclosed by brackets: [--option]
     */
    public function testItProvidesAnErrorMessageOnOptionsMissingBrackets()
    {
        $parser = new ExpressionParser();
        $parser->parse('greet --yell');
    }

    /**
     * @expectedException \Viserio\Contracts\Console\Exceptions\InvalidCommandExpression
     * @expectedExceptionMessage The expression was empty
     */
    public function testItProvidesAnErrorMessageOnEmpty()
    {
        $parser = new ExpressionParser();
        $parser->parse('');
    }

    public function assertParsesTo($expression, $expected)
    {
        $parser = new ExpressionParser();
        $this->assertEquals($expected, $parser->parse($expression));
    }
}
