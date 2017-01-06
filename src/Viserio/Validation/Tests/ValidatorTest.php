<?php
declare(strict_types=1);
namespace Viserio\Validation\Tests;

use PHPUnit\Framework\TestCase;
use Respect\Validation\Validator as RespectValidator;
use Viserio\Validation\Validator;

class ValidatorTest extends TestCase
{
    public function testValidate()
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

        self::assertInstanceOf(Validator::class, $validate);
        self::assertTrue($validate->passes());
        self::assertFalse($validate->fails());
        self::assertEquals(
            [
                'test' => true,
                'foo'  => true,
            ],
            $validate->valid()
        );
    }

    public function testValidateWithRegex()
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

        self::assertInstanceOf(Validator::class, $validate);
        self::assertTrue($validate->passes());
        self::assertFalse($validate->fails());
        self::assertEquals(
            [
                'test' => true,
            ],
            $validate->valid()
        );
    }

    public function testNotValidate()
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

        self::assertInstanceOf(Validator::class, $validate);
        self::assertFalse($validate->passes());
        self::assertTrue($validate->fails());
        self::assertEquals(
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

    public function testNotValidateWith2DatasAndOneRule()
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

        self::assertInstanceOf(Validator::class, $validate);
        self::assertFalse($validate->passes());
        self::assertTrue($validate->fails());
        self::assertEquals(
            [
                'foo' => [
                    'Foo must not contain letters (a-z)',
                ],
            ],
            $validate->invalid()
        );
    }

    public function testOptionalValidate()
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

        self::assertInstanceOf(Validator::class, $validate);
        self::assertTrue($validate->passes());
        self::assertFalse($validate->fails());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not (!) and optional (?) cant be used at the same time.
     */
    public function testThrowExceptionOnUseNotAndOptionalOnSameRuleValidate()
    {
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
