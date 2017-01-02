<?php
declare(strict_types=1);
namespace Viserio\Http\Tests\Uri\Filter;

use PHPUnit\Framework\TestCase;
use Viserio\Http\Uri\Filter\Query;

class QueryTest extends TestCase
{
    /**
     * @dataProvider queryProvider
     *
     * @param mixed $input
     * @param mixed $expected
     */
    public function testFilter($input, $expected)
    {
        $filter = new Query();
        $query  = $filter->parse($input);

        self::assertSame($expected, $filter->build($query));
    }

    public function queryProvider()
    {
        $unreserved = 'a-zA-Z0-9.-_~!$&\'()*+,;=:@';

        return [
            'string'                                          => ['kingkong=toto', 'kingkong=toto'],
            'empty string'                                    => ['', ''],
            'contains a reserved word #'                      => ['foo#bar', 'foo%23bar'],
            'contains a delimiter ?'                          => ['foo#bar', 'foo%23bar'],
            'key-only'                                        => ['k^ey', 'k%5Eey'],
            'key-value'                                       => ['k^ey=valu`', 'k%5Eey=valu%60'],
            'array-key-only'                                  => ['key[]', 'key%5B%5D'],
            'array-key-value'                                 => ['key[]=valu`', 'key%5B%5D=valu%60'],
            'complex'                                         => ['k^ey&key[]=valu`&f<>=`bar', 'k%5Eey&key%5B%5D=valu%60&f%3C%3E=%60bar'],
            'Percent encode spaces'                           => ['q=va lue', 'q=va%20lue'],
            'Percent encode multibyte'                        => ['€', '%E2%82%AC'],
            "Don't encode something that's already encoded"   => ['q=va%20lue', 'q=va%20lue'],
            'Percent encode invalid percent encodings'        => ['q=va%2-lue', 'q=va%252-lue'],
            "Don't encode path segments"                      => ['q=va/lue', 'q=va/lue'],
            "Don't encode unreserved chars or sub-delimiters" => [$unreserved, $unreserved],
            'Encoded unreserved chars are not decoded'        => ['q=v%61lue', 'q=v%61lue'],
        ];
    }
}
