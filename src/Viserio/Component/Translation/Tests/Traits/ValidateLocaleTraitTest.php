<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\Translation\Exception\InvalidArgumentException;
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
        $this->expectException(InvalidArgumentException::class);

        self::assertValidLocale($locale);
    }

    /**
     * @return array
     */
    public function wrongLocales(): array
    {
        return [
            ['?0'],
            [')(/'],
            ['/&_'],
        ];
    }
}
