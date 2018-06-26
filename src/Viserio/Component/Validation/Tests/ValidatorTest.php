<?php
declare(strict_types=1);
namespace Viserio\Component\Validation\Tests;

use PHPUnit\Framework\TestCase;
use Respect\Validation\Validator as RespectValidator;
use Viserio\Component\Validation\Validator;

/**
 * @internal
 */
final class ValidatorTest extends TestCase
{
    public function testValidate(): void
    {
        $validator = new Validator();
        $validate  = $validator->validate(
            [
                'test' => 'foo',
                'foo'  => 'foo',
            ],
            [
                'test' => 'alpha|noWhitespace|length:1,32',
                'foo'  => RespectValidator::alpha(),
            ]
        );

        static::assertInstanceOf(Validator::class, $validate);
        static::assertTrue($validate->passes());
        static::assertFalse($validate->fails());
        static::assertEquals(
            [
                'test' => true,
                'foo'  => true,
            ],
            $validate->valid()
        );
    }

    public function testValidateWithRegex(): void
    {
        $validator = new Validator();
        $validate  = $validator->validate(
            [
                'test' => 'foo',
            ],
            [
                'test' => 'regex:/^[A-z]+$/',
            ]
        );

        static::assertInstanceOf(Validator::class, $validate);
        static::assertTrue($validate->passes());
        static::assertFalse($validate->fails());
        static::assertEquals(
            [
                'test' => true,
            ],
            $validate->valid()
        );
    }

    public function testNotValidate(): void
    {
        $validator = new Validator();
        $validate  = $validator->validate(
            [
                'test' => 'foo ',
                'foo'  => 'aa',
            ],
            [
                'test' => '!alpha|noWhitespace|length:1,32',
                'foo'  => RespectValidator::not(RespectValidator::alpha()),
            ]
        );

        static::assertInstanceOf(Validator::class, $validate);
        static::assertFalse($validate->passes());
        static::assertTrue($validate->fails());
        static::assertSame(
            [
                'test' => [
                    'Test must not contain letters (a-z)',
                    'Test must not contain whitespace',
                ],
                'foo' => [
                    'Foo must not contain letters (a-z)',
                ],
            ],
            $validate->invalid()
        );
    }

    public function testNotValidateWith2DatasAndOneRule(): void
    {
        $validator = new Validator();
        $validate  = $validator->validate(
            [
                'test' => 'foo ',
                'foo'  => ['aa', 'bbb'],
            ],
            [
                'foo' => RespectValidator::not(RespectValidator::alpha()),
            ]
        );

        static::assertInstanceOf(Validator::class, $validate);
        static::assertFalse($validate->passes());
        static::assertTrue($validate->fails());
        static::assertEquals(
            [
                'foo' => [
                    'Foo must not contain letters (a-z)',
                ],
            ],
            $validate->invalid()
        );
    }

    public function testOptionalValidate(): void
    {
        $validator = new Validator();
        $validate  = $validator->validate(
            [
                'test' => ' ',
                'foo'  => '1',
            ],
            [
                'test' => '?alpha',
                'foo'  => '?numeric',
            ]
        );

        static::assertInstanceOf(Validator::class, $validate);
        static::assertTrue($validate->passes());
        static::assertFalse($validate->fails());
    }

    public function testThrowExceptionOnUseNotAndOptionalOnSameRuleValidate(): void
    {
        $this->expectException(\Viserio\Component\Contract\Validation\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Not (!) and optional (?) cant be used at the same time.');

        $validator = new Validator();
        $validator->validate(
            [
                'test' => ' ',
                'foo'  => '1',
            ],
            [
                'test' => '?alpha|!numeric',
            ]
        );
    }
}
