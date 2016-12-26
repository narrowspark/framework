<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Tests\Util;

use Viserio\WebProfiler\Util\TemplateHelper;

class TemplateHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testDump()
    {
        static::assertSame(
            trim('<pre class=sf-dump data-indent-pad="  "><span class=sf-dump-note>array:1</span> [<samp>
  "<span class=sf-dump-key>array</span>" => "<span class=sf-dump-str title="4 characters">test</span>"
</samp>]
</pre>'),
            $this->removeSymfonyVarDumper(TemplateHelper::dump(['array' => 'test']))
        );
    }

    public function testEscape()
    {
        $original = "This is a <a href=''>Foo</a> test string";

        $this->assertEquals(
            TemplateHelper::escape($original),
            "This is a &lt;a href=&#039;&#039;&gt;Foo&lt;/a&gt; test string"
        );
    }

    public function testEscapeBrokenUtf8()
    {
        // The following includes an illegal utf-8 sequence to test.
        // Encoded in base64 to survive possible encoding changes of this file.
        $original = base64_decode('VGhpcyBpcyBhbiBpbGxlZ2FsIHV0Zi04IHNlcXVlbmNlOiDD');

        // Test that the escaped string is kinda similar in length, not empty
        $this->assertLessThan(
            10,
            abs(strlen($original) - strlen(TemplateHelper::escape($original)))
        );
    }

    private function removeSymfonyVarDumper(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/', '', $html);

        return trim(preg_replace('/id=sf-dump-(?:\d+) /', '', $html));
    }
}
