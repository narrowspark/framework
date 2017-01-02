<?php
declare(strict_types=1);
namespace Viserio\Validation\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Viserio\Validation\Sanitizer;
use Viserio\Validation\Tests\Fixture\SanitizerFixture;
use Viserio\Validation\Tests\Fixture\SuffixFixture;

class SanitizerTest extends TestCase
{
    public function testThatSanitizerCanSanitizeWithClosure()
    {
        $sanitizer = new Sanitizer();
        $sanitizer->register('reverse', function ($field) {
            return strrev($field);
        });

        $data = ['name' => 'narrowspark'];

        $data = $sanitizer->sanitize(['name' => 'reverse'], $data);

        self::assertEquals('krapsworran', $data['name']);
    }

    public function testThatSanitizerCanSanitizeWithClass()
    {
        $sanitizer = new Sanitizer();
        $sanitizer->setContainer(new ArrayContainer([
            SanitizerFixture::class => new SanitizerFixture(),
        ]));
        $sanitizer->register('reverse', SanitizerFixture::class . '@foo');

        $data = ['name' => 'narrowspark'];

        $data = $sanitizer->sanitize(['name' => 'reverse'], $data);

        self::assertEquals('krapsworran', $data['name']);
    }

    public function testThatSanitizerCanSanitizeWithClosureAndParameters()
    {
        $sanitizer = new Sanitizer();
        $sanitizer->register('substring', function ($string, $start, $length) {
            return mb_substr($string, (int) $start, (int) $length);
        });

        $data = ['name' => 'narrowspark'];

        $data = $sanitizer->sanitize(['name' => 'substring:2,3'], $data);

        self::assertEquals('rro', $data['name']);
    }

    public function testThatSanitizerCanSanitizeWithClassAndParameters()
    {
        $sanitizer = new Sanitizer();
        $sanitizer->setContainer(new ArrayContainer([
            SuffixFixture::class => new SuffixFixture(),
        ]));
        $sanitizer->register('suffix', SuffixFixture::class . '@sanitize');

        $data = ['name' => 'Dayle'];

        $data = $sanitizer->sanitize(['name' => 'suffix:Rees'], $data);

        self::assertEquals('Dayle Rees', $data['name']);
    }

    public function testThatSanitizerCanSanitizeWithACallback()
    {
        $sanitizer = new Sanitizer();
        $sanitizer->register('reverse', [new SanitizerFixture(), 'foo']);

        $data = ['name' => 'Narrowspark'];

        $data = $sanitizer->sanitize(['name' => 'reverse'], $data);

        self::assertEquals('krapsworraN', $data['name']);
    }

    public function testThatSanitizerCanSanitizeWithACallbackAndParameters()
    {
        $sanitizer = new Sanitizer();
        $sanitizer->register('suffix', [new SuffixFixture(), 'sanitize']);

        $data = ['name' => 'Narrow'];

        $data = $sanitizer->sanitize(['name' => 'suffix:Spark'], $data);

        self::assertEquals('Narrow Spark', $data['name']);
    }

    public function testThatACallableRuleCanBeUsed()
    {
        $sanitizer = new Sanitizer();
        $data      = ['name' => 'Narrowspark'];

        $data = $sanitizer->sanitize(['name' => 'strrev'], $data);

        self::assertEquals('krapsworraN', $data['name']);
    }

    public function testThatACallableRuleCanBeUsedWithParameters()
    {
        $sanitizer = new Sanitizer();
        $data      = ['number' => '2435'];

        $data = $sanitizer->sanitize(['number' => 'str_pad:10,0,0'], $data);

        self::assertEquals('0000002435', $data['number']);
    }

    public function testThatSanitizerFunctionsWithMultipleRules()
    {
        $sanitizer = new Sanitizer();
        $data      = ['name' => '  Narrowspark_ !'];

        $sanitizer->register('alphabetize', function ($field) {
            return preg_replace('/[^a-zA-Z]/', null, $field);
        });

        $data = $sanitizer->sanitize(['name' => 'strrev|alphabetize|trim'], $data);

        self::assertEquals('krapsworraN', $data['name']);
    }

    public function testThatSanitizerFunctionsWithMultipleRulesWithParameters()
    {
        $sanitizer = new Sanitizer();
        $data      = ['name' => '  Dayle_ !'];

        $sanitizer->register('suffix', [new SuffixFixture(), 'sanitize']);

        $sanitizer->register('alphabetize', function ($field) {
            return preg_replace('/[^a-zA-Z]/', null, $field);
        });

        $data = $sanitizer->sanitize(['name' => 'suffix: Rees |strrev|alphabetize|trim'], $data);

        self::assertEquals('seeRelyaD', $data['name']);
    }

    public function testThatGlobalRulesCanBeSet()
    {
        $sanitizer = new Sanitizer();
        $data      = [
            'first_name' => ' Narrow',
            'last_name'  => 'Narrow ',
        ];

        $data = $sanitizer->sanitize([
            '*'         => 'trim|strtolower',
            'last_name' => 'strrev',
        ], $data);

        self::assertEquals([
            'first_name' => 'narrow',
            'last_name'  => 'worran',
        ], $data);
    }

    public function testThatGlobalRulesCanBeSetWithParameters()
    {
        $sanitizer = new Sanitizer();
        $data      = [
            'first_name' => ' Narrow',
            'last_name'  => 'Narrow ',
        ];

        $data = $sanitizer->sanitize([
            '*'         => 'trim|strtolower|substr:1',
            'last_name' => 'strrev',
        ], $data);

        self::assertEquals([
            'first_name' => 'arrow',
            'last_name'  => 'worra',
        ], $data);
    }
}
