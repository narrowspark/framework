<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\Traits\ValidateLocaleTrait;

class ValidateLocaleTraitTest extends TestCase
{
    use ValidateLocaleTrait;

    /**
     * @dataProvider wrongLocales
     *
     * @expectedException \InvalidArgumentException
     *
     * @param mixed $locale
     */
    public function testAssertValidLocaleToThrowException($locale): void
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
