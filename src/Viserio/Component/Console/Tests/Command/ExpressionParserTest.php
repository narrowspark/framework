<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Command;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Console\Command\ExpressionParser;
use Viserio\Component\Console\Input\InputArgument;
use Viserio\Component\Console\Input\InputOption;

class ExpressionParserTest extends TestCase
{
    public function testParsesCommandNames(): void
    {
        self::assertParsesTo('greet', [
            'name'      => 'greet',
            'arguments' => [],
            'options'   => [],
        ]);
    }

    public function testParsesCommandNamesContainingNamespaces(): void
    {
        self::assertParsesTo('demo:greet', [
            'name'      => 'demo:greet',
            'arguments' => [],
            'options'   => [],
        ]);
    }

    public function testParsesMandatoryArguments(): void
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

    public function testParsesOptionalArguments(): void
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

    public function testParsesArrayArguments(): void
    {
        self::assertParsesTo('greet [names=*]', [
            'name'      => 'greet',
            'arguments' => [
                new InputArgument('names', InputArgument::IS_ARRAY),
            ],
            'options' => [],
        ]);
    }

    public function testParsesArrayArgumentsWithAtLeastOneValue(): void
    {
        self::assertParsesTo('greet names=*', [
            'name'      => 'greet',
            'arguments' => [
                new InputArgument('names', InputArgument::IS_ARRAY | InputArgument::REQUIRED),
            ],
            'options' => [],
        ]);
    }

    public function testParsesOptions(): void
    {
        self::assertParsesTo('greet [--yell]', [
            'name'      => 'greet',
            'arguments' => [],
            'options'   => [
                new InputOption('yell', null, InputOption::VALUE_NONE),
            ],
        ]);
    }

    public function testParsesOptionsWithMandatoryValues(): void
    {
        self::assertParsesTo('greet [--iterations=]', [
            'name'      => 'greet',
            'arguments' => [],
            'options'   => [
                new InputOption('iterations', null, InputOption::VALUE_REQUIRED),
            ],
        ]);
    }

    public function testParsesOptionsWithMultipleValues(): void
    {
        self::assertParsesTo('greet [--name=*]', [
            'name'      => 'greet',
            'arguments' => [],
            'options'   => [
                new InputOption('name', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
            ],
        ]);
    }

    public function testParsesOptionsWithShortcuts(): void
    {
        self::assertParsesTo('greet [-y|--yell] [-it|--iterations=] [-n|--name=*]', [
            'name'      => 'greet',
            'arguments' => [],
            'options'   => [
                new InputOption('yell', 'y', InputOption::VALUE_NONE),
                new InputOption('iterations', 'it', InputOption::VALUE_REQUIRED),
                new InputOption('name', 'n', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
            ],
        ]);
    }

    public function testDefaultValueParsing(): void
    {
        self::assertParsesTo('command:name [argument=defaultArgumentValue] [--option=defaultOptionValue]', [
            'name'      => 'command:name',
            'arguments' => [
                new InputArgument('argument', InputArgument::OPTIONAL, '', 'defaultArgumentValue'),
            ],
            'options'   => [
                new InputOption('option', null, InputOption::VALUE_OPTIONAL, '', 'defaultOptionValue'),
            ],
        ]);
    }

    public function testDefaultValueParsingWithDiscription(): void
    {
        self::assertParsesTo('command:name [argument=defaultArgumentValue : The option description.] [--option=defaultOptionValue : The option description.]', [
            'name'      => 'command:name',
            'arguments' => [
                new InputArgument('argument', InputArgument::OPTIONAL, 'The option description.', 'defaultArgumentValue'),
            ],
            'options'   => [
                new InputOption('option', null, InputOption::VALUE_OPTIONAL, 'The option description.', 'defaultOptionValue'),
            ],
        ]);
    }

    public function testArrayValueParsing(): void
    {
        self::assertParsesTo('command:name [argument=*test,test2] [--option=*doptionValue, test]', [
            'name'      => 'command:name',
            'arguments' => [
                new InputArgument('argument', InputArgument::IS_ARRAY, '', ['test', 'test2']),
            ],
            'options'   => [
                new InputOption('option', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '', ['doptionValue', 'test']),
            ],
        ]);
    }

    public function testParserRegex(): void
    {
        self::assertParsesTo('greet test optional? foo-bar baz-foo=* [-y|--yell=hello] [argument=test]* names=* test= [argument_desc=test : description]', [
            'name'      => 'greet',
            'arguments' => [
                new InputArgument('test', InputArgument::REQUIRED),
                new InputArgument('optional', InputArgument::OPTIONAL),
                new InputArgument('foo-bar', InputArgument::REQUIRED),
                new InputArgument('baz-foo', InputArgument::IS_ARRAY | InputArgument::REQUIRED),
                new InputArgument('argument', InputArgument::OPTIONAL, '', 'test'),
                new InputArgument('names', InputArgument::IS_ARRAY | InputArgument::REQUIRED),
                new InputArgument('test', InputArgument::REQUIRED),
                new InputArgument('argument_desc', InputArgument::OPTIONAL, 'description', 'test'),
            ],
            'options'   => [
                new InputOption('yell', 'y', InputOption::VALUE_OPTIONAL, '', 'hello'),
            ],
        ]);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Console\Exception\InvalidCommandExpression
     * @expectedExceptionMessage An option must be enclosed by brackets: [--option]
     */
    public function testProvidesAnErrorMessageOnOptionsMissingBrackets(): void
    {
        ExpressionParser::parse('greet --yell');
    }

    /**
     * @expectedException \Viserio\Component\Contract\Console\Exception\InvalidCommandExpression
     * @expectedExceptionMessage The expression was empty.
     */
    public function testProvidesAnErrorMessageOnEmpty(): void
    {
        ExpressionParser::parse('');
    }

    /**
     * @param string $expression
     * @param array  $expected
     */
    protected static function assertParsesTo(string $expression, array $expected = []): void
    {
        self::assertEquals($expected, ExpressionParser::parse($expression));
    }
}
