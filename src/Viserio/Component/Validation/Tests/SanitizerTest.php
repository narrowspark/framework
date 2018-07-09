<?php
declare(strict_types=1);
namespace Viserio\Component\Validation\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Validation\Sanitizer;
use Viserio\Component\Validation\Tests\Fixture\SanitizerFixture;
use Viserio\Component\Validation\Tests\Fixture\SuffixFixture;

/**
 * @internal
 */
final class SanitizerTest extends TestCase
{
    public function testThatSanitizerCanSanitizeWithLambdaAndClosure(): void
    {
        $sanitizer = new Sanitizer();
        $sanitizer->register('reverse', function ($field) {
            return \strrev($field);
        });

        $data = $sanitizer->sanitize(['name' => 'reverse'], ['name' => 'narrowspark']);

        static::assertEquals('krapsworran', $data['name']);

        $data = $sanitizer->sanitize(['name' => 'plus'], ['name' => 'narrowspark']);

        static::assertEquals('narrowspark', $data['name']);
    }

    public function testThatSanitizerCanSanitizeWithClass(): void
    {
        $sanitizer = new Sanitizer();
        $sanitizer->setContainer(new ArrayContainer([
            SanitizerFixture::class => new SanitizerFixture(),
        ]));
        $sanitizer->register('reverse', SanitizerFixture::class . '@foo');

        $data = ['name' => 'narrowspark'];

        $data = $sanitizer->sanitize(['name' => 'reverse'], $data);

        static::assertEquals('krapsworran', $data['name']);
    }

    public function testThatSanitizerCanSanitizeWithClosureAndParameters(): void
    {
        $sanitizer = new Sanitizer();
        $sanitizer->register('substring', function ($string, $start, $length) {
            return \mb_substr($string, (int) $start, (int) $length);
        });

        $data = ['name' => 'narrowspark'];

        $data = $sanitizer->sanitize(['name' => 'substring:2,3'], $data);

        static::assertEquals('rro', $data['name']);
    }

    public function testThatSanitizerCanSanitizeWithClassAndParameters(): void
    {
        $sanitizer = new Sanitizer();
        $sanitizer->setContainer(new ArrayContainer([
            SuffixFixture::class => new SuffixFixture(),
        ]));
        $sanitizer->register('suffix', SuffixFixture::class . '@sanitize');

        $data = ['name' => 'Dayle'];

        $data = $sanitizer->sanitize(['name' => 'suffix:Rees'], $data);

        static::assertEquals('Dayle Rees', $data['name']);
    }

    public function testThatSanitizerCanSanitizeWithACallback(): void
    {
        $sanitizer = new Sanitizer();
        $sanitizer->register('reverse', [new SanitizerFixture(), 'foo']);

        $data = ['name' => 'Narrowspark'];

        $data = $sanitizer->sanitize(['name' => 'reverse'], $data);

        static::assertEquals('krapsworraN', $data['name']);
    }

    public function testThatSanitizerCanSanitizeWithACallbackAndParameters(): void
    {
        $sanitizer = new Sanitizer();
        $sanitizer->register('suffix', [new SuffixFixture(), 'sanitize']);

        $data = ['name' => 'Narrow'];

        $data = $sanitizer->sanitize(['name' => 'suffix:Spark'], $data);

        static::assertEquals('Narrow Spark', $data['name']);
    }

    public function testThatACallableRuleCanBeUsed(): void
    {
        $sanitizer = new Sanitizer();
        $data      = ['name' => 'Narrowspark'];

        $data = $sanitizer->sanitize(['name' => 'strrev'], $data);

        static::assertEquals('krapsworraN', $data['name']);
    }

    public function testThatACallableRuleCanBeUsedWithParameters(): void
    {
        $sanitizer = new Sanitizer();
        $data      = ['number' => '2435'];

        $data = $sanitizer->sanitize(['number' => 'str_pad:10,0,0'], $data);

        static::assertEquals('0000002435', $data['number']);
    }

    public function testThatSanitizerFunctionsWithMultipleRules(): void
    {
        $sanitizer = new Sanitizer();
        $data      = ['name' => '  Narrowspark_ !'];

        $sanitizer->register('alphabetize', function ($field) {
            return \preg_replace('/[^a-zA-Z]/', null, $field);
        });

        $data = $sanitizer->sanitize(['name' => 'strrev|alphabetize|trim'], $data);

        static::assertEquals('krapsworraN', $data['name']);
    }

    public function testThatSanitizerFunctionsWithMultipleRulesWithParameters(): void
    {
        $sanitizer = new Sanitizer();
        $data      = ['name' => '  Dayle_ !'];

        $sanitizer->register('suffix', [new SuffixFixture(), 'sanitize']);

        $sanitizer->register('alphabetize', function ($field) {
            return \preg_replace('/[^a-zA-Z]/', null, $field);
        });

        $data = $sanitizer->sanitize(['name' => 'suffix: Rees |strrev|alphabetize|trim'], $data);

        static::assertEquals('seeRelyaD', $data['name']);
    }

    public function testThatGlobalRulesCanBeSet(): void
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

        static::assertEquals([
            'first_name' => 'narrow',
            'last_name'  => 'worran',
        ], $data);
    }

    public function testThatGlobalRulesCanBeSetWithParameters(): void
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

        static::assertEquals([
            'first_name' => 'arrow',
            'last_name'  => 'worra',
        ], $data);
    }
}
