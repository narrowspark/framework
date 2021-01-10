<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Bridge\Twig\Tests\Node;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Twig\Compiler;
use Twig\Environment;
use Twig\Loader\LoaderInterface;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Node;
use Viserio\Bridge\Twig\Node\DumpNode;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class DumpNodeTest extends MockeryTestCase
{
    /** @var \Mockery\MockInterface|\Twig\Loader\LoaderInterface */
    private $loaderMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loaderMock = Mockery::mock(LoaderInterface::class);
    }

    public function testNoVar(): void
    {
        $node = new DumpNode('bar', null, 7);
        $env = new Environment($this->loaderMock);
        $compiler = new Compiler($env);
        $expected = <<<'EOTXT'
if ($this->env->isDebug()) {
    $barvars = [];
    foreach ($context as $barkey => $barval) {
        if (!$barval instanceof \Twig\Template) {
            $barvars[$barkey] = $barval;
        }
    }
    // line 7
    Symfony\Component\VarDumper\VarDumper::dump($barvars);
}

EOTXT;
        self::assertSame($expected, $compiler->compile($node)->getSource());
    }

    public function testIndented(): void
    {
        $node = new DumpNode('bar', null, 7);
        $env = new Environment($this->loaderMock);
        $compiler = new Compiler($env);
        $expected = <<<'EOTXT'
    if ($this->env->isDebug()) {
        $barvars = [];
        foreach ($context as $barkey => $barval) {
            if (!$barval instanceof \Twig\Template) {
                $barvars[$barkey] = $barval;
            }
        }
        // line 7
        Symfony\Component\VarDumper\VarDumper::dump($barvars);
    }

EOTXT;
        self::assertSame($expected, $compiler->compile($node, 1)->getSource());
    }

    public function testOneVar(): void
    {
        $vars = new Node([
            new NameExpression('foo', 7),
        ]);
        $node = new DumpNode('bar', $vars, 7);
        $env = new Environment($this->loaderMock);
        $compiler = new Compiler($env);
        $expected = <<<'EOTXT'
if ($this->env->isDebug()) {
    // line 7
    Symfony\Component\VarDumper\VarDumper::dump(%foo%);
}

EOTXT;
        $expected = \preg_replace('/%(.*?)%/', '($context["$1"] ?? null)', $expected);

        self::assertSame($expected, $compiler->compile($node)->getSource());
    }

    public function testMultiVars(): void
    {
        $vars = new Node([
            new NameExpression('foo', 7),
            new NameExpression('bar', 7),
        ]);
        $node = new DumpNode('bar', $vars, 7);
        $env = new Environment($this->loaderMock);
        $compiler = new Compiler($env);
        $expected = <<<'EOTXT'
if ($this->env->isDebug()) {
    // line 7
    Symfony\Component\VarDumper\VarDumper::dump(array(
        "foo" => %foo%,
        "bar" => %bar%,
    ));
}

EOTXT;
        $expected = \preg_replace('/%(.*?)%/', '($context["$1"] ?? null)', $expected);

        self::assertSame($expected, $compiler->compile($node)->getSource());
    }
}
