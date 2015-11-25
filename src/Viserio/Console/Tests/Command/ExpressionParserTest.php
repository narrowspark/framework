<?php
namespace Viserio\Console\Test\Command;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.10.0
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Viserio\Console\Command\ExpressionParser;
use Viserio\Console\Input\InputArgument;
use Viserio\Console\Input\InputOption;

/**
 * ApplicationTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
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
    public function itParses_mandatoryArguments()
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
