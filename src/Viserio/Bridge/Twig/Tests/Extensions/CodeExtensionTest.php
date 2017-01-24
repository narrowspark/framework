<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extensions;

use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Twig\Extensions\CodeExtension;

class CodeExtensionTest extends TestCase
{
    /**
     * @dataProvider getClassNameProvider
     * @param mixed $class
     * @param mixed $abbr
     */
    public function testGettingClassAbbreviation($class, $abbr)
    {
        $this->assertEquals($this->getExtension()->abbrClass($class), $abbr);
    }

    /**
     * @dataProvider getMethodNameProvider
     * @param mixed $method
     * @param mixed $abbr
     */
    public function testGettingMethodAbbreviation($method, $abbr)
    {
        $this->assertEquals($this->getExtension()->abbrMethod($method), $abbr);
    }

    public function getClassNameProvider()
    {
        return [
            ['F\Q\N\Foo', '<abbr title="F\Q\N\Foo">Foo</abbr>'],
            ['Bare', '<abbr title="Bare">Bare</abbr>'],
        ];
    }

    public function getMethodNameProvider()
    {
        return [
            ['F\Q\N\Foo::Method', '<abbr title="F\Q\N\Foo">Foo</abbr>::Method()'],
            ['Bare::Method', '<abbr title="Bare">Bare</abbr>::Method()'],
            ['Closure', '<abbr title="Closure">Closure</abbr>'],
            ['Method', '<abbr title="Method">Method</abbr>()'],
        ];
    }

    public function testGetName()
    {
        $this->assertEquals('Viserio_Bridge_Twig_Extension_Code', $this->getExtension()->getName());
    }

    protected function getExtension()
    {
        return new CodeExtension('proto://%f#&line=%l&' . mb_substr(__FILE__, 0, 5) . '>foobar', '/root', 'UTF-8');
    }
}
