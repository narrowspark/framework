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
     * @expectedException \InvalidArgumentException
     * @param mixed $locale
     */
    public function testAssertValidLocaleToThrowException($locale)
    {
        self::assertValidLocale($locale);
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
