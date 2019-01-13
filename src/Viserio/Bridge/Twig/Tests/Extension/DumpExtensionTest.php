<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extension;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\VarDumper\VarDumper;
use Throwable;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\LoaderInterface;
use Viserio\Bridge\Twig\Extension\DumpExtension;

/**
 * @internal
 */
final class DumpExtensionTest extends MockeryTestCase
{
    /**
     * @dataProvider getDumpTags
     *
     * @param mixed $template
     * @param mixed $debug
     * @param mixed $expectedOutput
     * @param mixed $expectedDumped
     *
     * @throws \Throwable
     *
     * @return void
     */
    public function testDumpTag($template, $debug, $expectedOutput, $expectedDumped): void
    {
        $twig = new Environment(new ArrayLoader(['template' => $template]), [
            'debug'         => $debug,
            'cache'         => false,
            'optimizations' => 0,
        ]);
        $twig->addExtension(new DumpExtension());

        $dumped     = null;
        $exception  = null;
        $prevDumper = VarDumper::setHandler(static function ($var) use (&$dumped): void {
            $dumped = $var;
        });

        try {
            $this->assertEquals($expectedOutput, $twig->render('template'));
        } catch (Throwable $exception) {
        }

        VarDumper::setHandler($prevDumper);

        if ($exception !== null) {
            throw $exception;
        }

        $this->assertSame($expectedDumped, $dumped);
    }

    /**
     * @return array
     */
    public function getDumpTags(): array
    {
        return [
            ['A{% dump %}B', true, 'AB', []],
            ['A{% set foo="bar"%}B{% dump %}C', true, 'ABC', ['foo' => 'bar']],
            ['A{% dump %}B', false, 'AB', null],
        ];
    }

    /**
     * @dataProvider getDumpArgs
     *
     * @param array  $context
     * @param array  $args
     * @param string $expectedOutput
     * @param bool   $debug
     *
     * @return void
     */
    public function testDump(array $context, array $args, string $expectedOutput, bool $debug = true): void
    {
        $extension = new DumpExtension();
        $twig      = new Environment($this->mock(LoaderInterface::class), [
            'debug'         => $debug,
            'cache'         => false,
            'optimizations' => 0,
        ]);

        \array_unshift($args, $context);
        \array_unshift($args, $twig);

        $dump = $extension->dump(...$args);

        if ($debug) {
            $this->assertStringStartsWith('<script>', $dump);
            $dump = \preg_replace('/^.*?<pre/', '<pre', $dump);
            $dump = \preg_replace('/sf-dump-\d+/', 'sf-dump', $dump);
        }

        $this->assertEquals($expectedOutput, $dump);
    }

    public function getDumpArgs(): array
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
        $this->assertEquals('Viserio_Bridge_Twig_Extension_Dump', (new DumpExtension())->getName());
    }
}
