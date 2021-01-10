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

namespace Viserio\Bridge\Twig\Tests\Extension;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper;
use Throwable;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\LoaderInterface;
use Viserio\Bridge\Twig\Extension\DumpExtension;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class DumpExtensionTest extends MockeryTestCase
{
    /** @var \Viserio\Bridge\Twig\Extension\DumpExtension */
    private $extension;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->extension = new DumpExtension(new VarCloner(), new HtmlDumper());

        parent::setUp();
    }

    /**
     * @dataProvider provideDumpTagCases
     *
     * @throws Throwable
     */
    public function testDumpTag(string $template, bool $debug, string $expectedOutput, ?array $expectedDumped): void
    {
        $twig = new Environment(new ArrayLoader(['template' => $template]), [
            'debug' => $debug,
            'cache' => false,
            'optimizations' => 0,
        ]);
        $twig->addExtension($this->extension);

        $dumped = null;
        $exception = null;
        $prevDumper = VarDumper::setHandler(static function (array $var) use (&$dumped): void {
            $dumped = $var;
        });

        try {
            self::assertEquals($expectedOutput, $twig->render('template'));
        } catch (Throwable $exception) {
        }

        VarDumper::setHandler($prevDumper);

        if ($exception !== null) {
            throw $exception;
        }

        self::assertSame($expectedDumped, $dumped);
    }

    public static function provideDumpTagCases(): iterable
    {
        return [
            ['A{% dump %}B', true, 'AB', []],
            ['A{% set foo="bar"%}B{% dump %}C', true, 'ABC', ['foo' => 'bar']],
            ['A{% dump %}B', false, 'AB', null],
        ];
    }

    /**
     * @dataProvider provideDumpCases
     */
    public function testDump(array $context, array $args, string $expectedOutput, bool $debug = true): void
    {
        $twig = new Environment(Mockery::mock(LoaderInterface::class), [
            'debug' => $debug,
            'cache' => false,
            'optimizations' => 0,
        ]);

        \array_unshift($args, $context);
        \array_unshift($args, $twig);

        $dump = $this->extension->dump(...$args);

        if ($debug) {
            self::assertStringStartsWith('<script>', $dump);
            $dump = \preg_replace('/^.*?<pre/', '<pre', $dump);
            $dump = \preg_replace('/sf-dump-\d+/', 'sf-dump', $dump);
        }

        self::assertEquals($expectedOutput, $dump);
    }

    public static function provideDumpCases(): iterable
    {
        return [
            [[], [], '', false],
            [[], [], "<pre class=sf-dump id=sf-dump data-indent-pad=\"  \">[]\n</pre><script>Sfdump(\"sf-dump\")</script>\n"],
            [
                [],
                [123, 456],
                "<pre class=sf-dump id=sf-dump data-indent-pad=\"  \"><span class=sf-dump-num>123</span>\n</pre><script>Sfdump(\"sf-dump\")</script>\n"
                . "<pre class=sf-dump id=sf-dump data-indent-pad=\"  \"><span class=sf-dump-num>456</span>\n</pre><script>Sfdump(\"sf-dump\")</script>\n",
            ],
            [
                ['foo' => 'bar'],
                [],
                "<pre class=sf-dump id=sf-dump data-indent-pad=\"  \"><span class=sf-dump-note>array:1</span> [<samp>\n"
                . "  \"<span class=sf-dump-key>foo</span>\" => \"<span class=sf-dump-str title=\"3 characters\">bar</span>\"\n"
                . "</samp>]\n"
                . "</pre><script>Sfdump(\"sf-dump\")</script>\n",
            ],
        ];
    }

    public function testGetName(): void
    {
        self::assertEquals('Viserio_Bridge_Twig_Extension_Dump', $this->extension->getName());
    }
}
