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

        $this->assertInstanceOf(Validator::class, $validate);
        $this->assertTrue($validate->passes());
        $this->assertFalse($validate->fails());
        $this->assertEquals(
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

        $this->assertInstanceOf(Validator::class, $validate);
        $this->assertTrue($validate->passes());
        $this->assertFalse($validate->fails());
        $this->assertEquals(
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

        $this->assertInstanceOf(Validator::class, $validate);
        $this->assertFalse($validate->passes());
        $this->assertTrue($validate->fails());
        $this->assertEquals(
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

        $this->assertInstanceOf(Validator::class, $validate);
        $this->assertFalse($validate->passes());
        $this->assertTrue($validate->fails());
        $this->assertEquals(
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

        $this->assertInstanceOf(Validator::class, $validate);
        $this->assertTrue($validate->passes());
        $this->assertFalse($validate->fails());
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
