<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Command;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Console\Command\ExpressionParser;
use Viserio\Component\Console\Input\InputArgument;
use Viserio\Component\Console\Input\InputOption;

class ExpressionParserTest extends TestCase
{
    public function testItParsesCommandNames()
    {
        self::assertParsesTo('greet', [
            'name'      => 'greet',
            'arguments' => [],
            'options'   => [],
        ]);
    }

    public function testItParsesCommandNamesContainingNamespaces()
    {
        self::assertParsesTo('demo:greet', [
            'name'      => 'demo:greet',
            'arguments' => [],
            'options'   => [],
        ]);
    }

    public function testItParsesMandatoryArguments()
    {
        self::assertParsesTo('greet firstname lastname', [
            'name'      => 'greet',
            'arguments' => [
                new InputArgument('firstname', InputArgument::REQUIRED),
                new InputArgument('lastname', InputArgument::REQUIRED),
            ],
            'options' => [],
        ]);
    }

    public function testItParsesOptionalArguments()
    {
        self::assertParsesTo('greet [firstname] [lastname]', [
            'name'      => 'greet',
            'arguments' => [
                new InputArgument('firstname', InputArgument::OPTIONAL),
                new InputArgument('lastname', InputArgument::OPTIONAL),
            ],
            'options' => [],
        ]);
    }

    public function testItParsesArrayArguments()
    {
        self::assertParsesTo('greet [names]*', [
            'name'      => 'greet',
            'arguments' => [
                new InputArgument('names', InputArgument::IS_ARRAY),
            ],
            'options' => [],
        ]);
    }

    public function testItParsesArrayArgumentsWithAtLeastOneValue()
    {
        self::assertParsesTo('greet names*', [
            'name'      => 'greet',
            'arguments' => [
                new InputArgument('names', InputArgument::IS_ARRAY | InputArgument::REQUIRED),
            ],
            'options' => [],
        ]);
    }

    public function testItParsesOptions()
    {
        self::assertParsesTo('greet [--yell]', [
            'name'      => 'greet',
            'arguments' => [],
            'options'   => [
                new InputOption('yell', null, InputOption::VALUE_NONE),
            ],
        ]);
    }

    public function testItParsesOptionsWithMandatoryValues()
    {
        self::assertParsesTo('greet [--iterations=]', [
            'name'      => 'greet',
            'arguments' => [],
            'options'   => [
                new InputOption('iterations', null, InputOption::VALUE_REQUIRED),
            ],
        ]);
    }

    public function testItParsesOptionsWithMultipleValues()
    {
        self::assertParsesTo('greet [--name=]*', [
            'name'      => 'greet',
            'arguments' => [],
            'options'   => [
                new InputOption('name', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
            ],
        ]);
    }

    public function testItParsesOptionsWithShortcuts()
    {
        self::assertParsesTo('greet [-y|--yell] [-it|--iterations=] [-n|--name=]*', [
            'name'      => 'greet',
            'arguments' => [],
            'options'   => [
                new InputOption('yell', 'y', InputOption::VALUE_NONE),
                new InputOption('iterations', 'it', InputOption::VALUE_REQUIRED),
                new InputOption('name', 'n', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
            ],
        ]);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Console\Exceptions\InvalidCommandExpression
     * @expectedExceptionMessage An option must be enclosed by brackets: [--option]
     */
    public function testItProvidesAnErrorMessageOnOptionsMissingBrackets()
    {
        $parser = new ExpressionParser();
        $parser->parse('greet --yell');
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Console\Exceptions\InvalidCommandExpression
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

        self::assertEquals($expected, $parser->parse($expression));
    }
}
