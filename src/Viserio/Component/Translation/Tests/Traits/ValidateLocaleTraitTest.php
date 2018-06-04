<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\Traits\ValidateLocaleTrait;

/**
 * @internal
 */
final class ValidateLocaleTraitTest extends TestCase
{
    use ValidateLocaleTrait;

    /**
     * @dataProvider wrongLocales
     *
     * @param mixed $locale
     */
    public function testAssertValidLocaleToThrowException($locale): void
    {
        $this->expectException(\InvalidArgumentException::class);

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
