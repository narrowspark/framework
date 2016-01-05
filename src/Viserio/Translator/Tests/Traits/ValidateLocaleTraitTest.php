<?php
namespace Viserio\Translator\Tests\Traits;

use Viserio\Translator\Traits\ValidateLocaleTrait;

class ValidateLocaleTraitTest extends \PHPUnit_Framework_TestCase
{
    use ValidateLocaleTrait;

    /**
     * @dataProvider wrongLocales
     *
     * @expectedException InvalidArgumentException
     */
    public function testAssertValidLocaleToThrowException($locale)
    {
        $this->assertValidLocale($locale);
    }

    public function wrongLocales()
    {
        return [
            ['?0'],
            [')(/'],
            ['/&_'],
        ];
    }
}
