<?php
declare(strict_types=1);
namespace Viserio\Translation\Tests\Traits;

use Viserio\Translation\Traits\ValidateLocaleTrait;

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
