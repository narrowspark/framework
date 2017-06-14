<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extension;

use PHPUnit\Framework\TestCase;
use Symfony\Component\VarDumper\VarDumper;
use Throwable;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\LoaderInterface;
use Viserio\Bridge\Twig\Extension\DumpExtension;

class DumpExtensionTest extends TestCase
{
    /**
     * @dataProvider getDumpTags
     *
     * @param mixed $template
     * @param mixed $debug
     * @param mixed $expectedOutput
     * @param mixed $expectedDumped
     */
    public function testDumpTag($template, $debug, $expectedOutput, $expectedDumped)
    {
        $twig = new Environment(new ArrayLoader(['template' => $template]), [
            'debug'         => $debug,
            'cache'         => false,
            'optimizations' => 0,
        ]);
        $twig->addExtension(new DumpExtension());

        $dumped     = null;
        $exception  = null;
        $prevDumper = VarDumper::setHandler(function ($var) use (&$dumped) {
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

    public function getDumpTags()
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
     * @param mixed $context
     * @param mixed $args
     * @param mixed $expectedOutput
     * @param mixed $debug
     */
    public function testDump($context, $args, $expectedOutput, $debug = true)
    {
        $extension = new DumpExtension();
        $twig      = new Environment($this->getMockBuilder(LoaderInterface::class)->getMock(), [
            'debug'         => $debug,
            'cache'         => false,
            'optimizations' => 0,
        ]);

        array_unshift($args, $context);
        array_unshift($args, $twig);

        $dump = call_user_func_array([$extension, 'dump'], $args);

        if ($debug) {
            self::assertStringStartsWith('<script>', $dump);
            $dump = preg_replace('/^.*?<pre/', '<pre', $dump);
            $dump = preg_replace('/sf-dump-\d+/', 'sf-dump', $dump);
        }

        self::assertEquals($expectedOutput, $dump);
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

    public function testGetName()
    {
        self::assertEquals('Viserio_Bridge_Twig_Extension_Dump', (new DumpExtension())->getName());
    }
}
