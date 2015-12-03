<?php
namespace Viserio\Console\Tests\Command;

use Viserio\Console\Command\ExpressionParser;
use Viserio\Console\Input\InputArgument;
use Viserio\Console\Input\InputOption;

class ExpressionParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itParsesCommandNames()
    {
        $this->assertParsesTo('greet', [
            'name' => 'greet',
            'arguments' => [],
            'options' => [],
        ]);
    }

    /**
     * @test
     */
    public function itParsesCommandNamesContainingNamespaces()
    {
        $this->assertParsesTo('demo:greet', [
            'name' => 'demo:greet',
            'arguments' => [],
            'options' => [],
        ]);
    }

    /**
     * @test
     */
    public function itParsesMandatoryArguments()
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

    /**
     * @test
     */
    public function itParsesOptionalArguments()
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

    /**
     * @test
     */
    public function itParsesArrayArguments()
    {
        $this->assertParsesTo('greet [names]*', [
            'name' => 'greet',
            'arguments' => [
                new InputArgument('names', InputArgument::IS_ARRAY),
            ],
            'options' => [],
        ]);
    }

    /**
     * @test
     */
    public function itParsesArrayArgumentsWithAtLeastOneValue()
    {
        $this->assertParsesTo('greet names*', [
            'name' => 'greet',
            'arguments' => [
                new InputArgument('names', InputArgument::IS_ARRAY | InputArgument::REQUIRED),
            ],
            'options' => [],
        ]);
    }

    /**
     * @test
     */
    public function itParsesOptions()
    {
        $this->assertParsesTo('greet [--yell]', [
            'name' => 'greet',
            'arguments' => [],
            'options' => [
                new InputOption('yell', null, InputOption::VALUE_NONE),
            ],
        ]);
    }

    /**
     * @test
     */
    public function itParsesOptionsWithMandatoryValues()
    {
        $this->assertParsesTo('greet [--iterations=]', [
            'name' => 'greet',
            'arguments' => [],
            'options' => [
                new InputOption('iterations', null, InputOption::VALUE_REQUIRED),
            ],
        ]);
    }

    /**
     * @test
     */
    public function itParsesOptionsWithMultipleValues()
    {
        $this->assertParsesTo('greet [--name=]*', [
            'name' => 'greet',
            'arguments' => [],
            'options' => [
                new InputOption('name', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
            ],
        ]);
    }

    /**
     * @test
     */
    public function itParsesOptionsWithShortcuts()
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
     * @test
     * @expectedException \Viserio\Contracts\Console\Command\InvalidCommandExpression
     * @expectedExceptionMessage An option must be enclosed by brackets: [--option]
     */
    public function itProvidesAnErrorMessageOnOptionsMissingBrackets()
    {
        $parser = new ExpressionParser();
        $parser->parse('greet --yell');
    }

    public function assertParsesTo($expression, $expected)
    {
        $parser = new ExpressionParser();
        $this->assertEquals($expected, $parser->parse($expression));
    }
}
