<?php
namespace Viserio\Translation\Tests;

use Viserio\Translation\TransChecker;

class TransCheckerTest extends \PHPUnit_Framework_TestCase
{
    private $checker;

    public function setUp()
    {
        $this->checker = new TransChecker();
    }

    public function testGetDefaultLocale()
    {
        $this->assertSame('en', $this->checker->getDefaultLocale());
    }

    public function testGetLocales()
    {
        $locals = $this->checker->getLocales();

        $this->assertSame('en', $locals[0]);
    }

    public function testGetIgnoredTranslations()
    {
        $ignored = $this->checker->getIgnoredTranslations();

        $this->assertSame('de', $ignored[0]);
    }

    public function testCheck()
    {
        $checked = $this->checker->check();
    }
}
