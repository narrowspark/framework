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
    }
}
