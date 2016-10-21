<?php
declare(strict_types=1);
namespace Viserio\Validation\Tests;

use Respect\Validation\Validator as RespectValidator;
use Viserio\Validation\Validator;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testValidate()
    {
        $validator = new Validator();
        $validate = $validator->validate(
            [
                'test' => 'foo',
                'foo' => 'foo',
            ],
            [
                'test' => 'alpha|noWhitespace|length:1,32',
                'foo' => RespectValidator::alpha(),
            ]
        );

        $this->assertInstanceOf(Validator::class, $validate);
        $this->assertTrue($validate->passes());
        $this->assertFalse($validate->fails());
        $this->assertEquals(
            [
                'test' => true,
                'foo' => true
            ],
            $validate->valid()
        );
    }

    public function testValidateWithRegex()
    {
        $validator = new Validator();
        $validate = $validator->validate(
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


    public function testNotValidate()
    {
        $validator = new Validator();
        $validate = $validator->validate(
            [
                'test' => 'foo ',
                'foo' => 'aa',
            ],
            [
                'test' => '!alpha|noWhitespace|length:1,32',
                'foo' => RespectValidator::not(RespectValidator::alpha()),
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

    public function testNotValidateWith2DatasAndOneRule()
    {
        $validator = new Validator();
        $validate = $validator->validate(
            [
                'test' => 'foo ',
                'foo' => ['aa', 'bbb'],
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

    public function testOptionalValidate()
    {
        $validator = new Validator();
        $validate = $validator->validate(
            [
                'test' => ' ',
                'foo' => '1',
            ],
            [
                'test' => '?alpha',
                'foo' => '?numeric',
            ]
        );

        $this->assertInstanceOf(Validator::class, $validate);
        $this->assertTrue($validate->passes());
        $this->assertFalse($validate->fails());
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
                'foo' => '1',
            ],
            [
                'test' => '?alpha|!numeric',
            ]
        );
    }
}
