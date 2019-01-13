<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Util;

use DOMDocument;
use Exception;
use InvalidArgumentException;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use org\bovigo\vfs\vfsStream;
use Viserio\Component\Contract\Parser\Exception\FileNotFoundException;
use Viserio\Component\Parser\Utils\XmlUtils;

/**
 * This file has been ported from Symfony. The original
 * code is (c) Fabien Potencier <fabien@symfony.com>.
 *
 * @internal
 */
final class XmlUtilsTest extends MockeryTestCase
{
    /**
     * @var string
     */
    private $fixturesPath;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->root         = vfsStream::setup();
        $this->fixturesPath = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Utils' . \DIRECTORY_SEPARATOR;
    }

    public function testLoadFileToThrowException(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('No such file [nonexistfile] found.');

        XmlUtils::loadFile('nonexistfile');
    }

    public function testLoadFileWithError77(): void
    {
        $file = vfsStream::newFile('invalid.xml')->withContent(
            '<?xml version="1.0" encoding="UTF-8"?>
<root>
            '
        )->at($this->root);

        try {
            XmlUtils::loadFile($file->url());
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertContains('ERROR 77', $e->getMessage());
        }
    }

    public function testLoadFileWithDocumentTypes(): void
    {
        $file = vfsStream::newFile('document_type.xml')->withContent(
            '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE scan [<!ENTITY test SYSTEM "php://filter/read=convert.base64-encode/resource={{ resource }}">]>
<scan></scan>
            '
        )->at($this->root);

        try {
            XmlUtils::loadFile($file->url());
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertContains('Document types are not allowed', $e->getMessage());
        }
    }

    public function testLoadFileWithError1845(): void
    {
        $file = vfsStream::newFile('invalid_schema.xml')->withContent(
            '<?xml version="1.0" encoding="UTF-8"?>
<root2 xmlns="http://example.com/schema" />
            '
        )->at($this->root);

        $schemaFile = vfsStream::newFile('schema.xsd')->withContent(
            '<?xml version="1.0" encoding="UTF-8"?>

<xsd:schema xmlns="http://example.com/schema"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    targetNamespace="http://example.com/schema"
    elementFormDefault="qualified">

  <xsd:element name="root" />
</xsd:schema>
            '
        )->at($this->root);

        try {
            XmlUtils::loadFile($file->url(), $schemaFile->url());
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertContains('ERROR 1845', $e->getMessage());
        }
    }

    public function testLoadFileWithInvalidCallback(): void
    {
        $file = vfsStream::newFile('invalid_schema.xml')->withContent(
            '<?xml version="1.0" encoding="UTF-8"?>
<root2 xmlns="http://example.com/schema" />
            '
        )->at($this->root);

        try {
            XmlUtils::loadFile($file->url(), 'invalid_callback_or_file');
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertContains('XSD file or callable', $e->getMessage());
        }
    }

    public function testLoadFileWithValidCallback(): void
    {
        $file = vfsStream::newFile('valid.xml')->withContent(
            '<?xml version="1.0" encoding="UTF-8"?>
<root xmlns="http://example.com/schema">
</root>
            '
        )->at($this->root);

        $validatorMock = $this->mock(Validator::class);
        $validatorMock->shouldReceive('validate')
            ->once()
            ->andReturn(false);
        $validatorMock->shouldReceive('validate')
            ->once()
            ->andReturn(true);

        try {
            XmlUtils::loadFile($file->url(), [$validatorMock, 'validate']);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertContains('is not valid', $e->getMessage());
        }

        $this->assertInstanceOf(DOMDocument::class, XmlUtils::loadFile($file->url(), [$validatorMock, 'validate']));
        $this->assertSame([], \libxml_get_errors());
    }

    public function testLoadFileWithInternalErrorsEnabled(): void
    {
        $file = vfsStream::newFile('invalid_schema.xml')->withContent(
            '<?xml version="1.0" encoding="UTF-8"?>
<root2 xmlns="http://example.com/schema" />
            '
        )->at($this->root);

        $internalErrors = \libxml_use_internal_errors(true);

        $this->assertSame([], \libxml_get_errors());
        $this->assertInstanceOf(DOMDocument::class, XmlUtils::loadFile($file->url()));
        $this->assertSame([], \libxml_get_errors());

        \libxml_clear_errors();
        \libxml_use_internal_errors($internalErrors);
    }

    /**
     * @dataProvider getDataForConvertDomToArray
     *
     * @param null|array|string $expected
     * @param string            $xml
     * @param bool              $root
     * @param bool              $checkPrefix
     */
    public function testConvertDomToArray($expected, string $xml, bool $root = false, bool $checkPrefix = true): void
    {
        $dom = new DOMDocument();
        $dom->loadXML($root === true ? $xml : '<root>' . $xml . '</root>');

        $this->assertSame($expected, XmlUtils::convertDomElementToArray($dom->documentElement, $checkPrefix));
    }

    public function getDataForConvertDomToArray(): array
    {
        return [
            [null, ''],
            ['bar', 'bar'],
            [['bar' => 'foobar'], '<foo bar="foobar" />', true],
            [['foo' => null], '<foo />'],
            [['foo' => 'bar'], '<foo>bar</foo>'],
            [['foo' => ['foo' => 'bar']], '<foo foo="bar"/>'],
            [['foo' => ['foo' => 0]], '<foo><foo>0</foo></foo>'],
            [['foo' => ['foo' => 'bar']], '<foo><foo>bar</foo></foo>'],
            [['foo' => ['foo' => 'bar', 'value' => 'text']], '<foo foo="bar">text</foo>'],
            [['foo' => ['attr' => 'bar', 'foo' => 'text']], '<foo attr="bar"><foo>text</foo></foo>'],
            [['foo' => ['bar', 'text']], '<foo>bar</foo><foo>text</foo>'],
            [['foo' => [['foo' => 'bar'], ['foo' => 'text']]], '<foo foo="bar"/><foo foo="text" />'],
            [['foo' => ['foo' => ['bar', 'text']]], '<foo foo="bar"><foo>text</foo></foo>'],
            [['foo' => 'bar'], '<foo><!-- Comment -->bar</foo>'],
            [['foo' => 'text'], '<foo xmlns:h="http://www.example.org/bar" h:bar="bar">text</foo>'],
            [['foo' => ['bar' => 'bar', 'value' => 'text']], '<foo xmlns:h="http://www.example.org/bar" h:bar="bar">text</foo>', false, false],
            [['attr' => 1, 'b' => 'hello'], '<foo:a xmlns:foo="http://www.example.org/foo" xmlns:h="http://www.example.org/bar" attr="1" h:bar="bar"><foo:b>hello</foo:b><h:c>2</h:c></foo:a>', true],
        ];
    }

    /**
     * @dataProvider getDataForPhpize
     *
     * @param mixed $expected
     * @param mixed $value
     */
    public function testPhpize($expected, $value): void
    {
        $this->assertSame($expected, XmlUtils::phpize($value));
    }

    public function getDataForPhpize(): array
    {
        return [
            ['', ''],
            [null, 'null'],
            [true, 'true'],
            [false, 'false'],
            [null, 'Null'],
            [true, 'True'],
            [false, 'False'],
            [0, '0'],
            [1, '1'],
            [-1, '-1'],
            [0777, '0777'],
            [255, '0xFF'],
            [100.0, '1e2'],
            [-120.0, '-1.2E2'],
            [-10100.1, '-10100.1'],
            ['-10,100.1', '-10,100.1'],
            ['1234 5678 9101 1121 3141', '1234 5678 9101 1121 3141'],
            ['1,2,3,4', '1,2,3,4'],
            ['11,22,33,44', '11,22,33,44'],
            ['11,222,333,4', '11,222,333,4'],
            ['1,222,333,444', '1,222,333,444'],
            ['11,222,333,444', '11,222,333,444'],
            ['111,222,333,444', '111,222,333,444'],
            ['1111,2222,3333,4444,5555', '1111,2222,3333,4444,5555'],
            ['foo', 'foo'],
            [6, '0b0110'],
            [6.1, '6.1'],
        ];
    }

    public function testLoadEmptyXmlFile(): void
    {
        $file = vfsStream::newFile('foo.xml')->withContent(
            '
            '
        )->at($this->root);

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Content does not contain valid XML, it is empty.');

        XmlUtils::loadFile($file->url());
    }

    // test for issue https://github.com/symfony/symfony/issues/9731
    public function testLoadWrongEmptyXMLWithErrorHandler(): void
    {
        $file = vfsStream::newFile('foo.xml')->withContent(
            '
            '
        )->at($this->root);
        $originalDisableEntities = \libxml_disable_entity_loader(false);
        $errorReporting          = \error_reporting(-1);

        \set_error_handler(static function ($errno, $errstr): void {
            throw new Exception($errstr, $errno);
        });

        try {
            try {
                XmlUtils::loadFile($file->url());
                $this->fail('An exception should have been raised');
            } catch (InvalidArgumentException $e) {
                $this->assertEquals('Content does not contain valid XML, it is empty.', $e->getMessage());
            }
        } finally {
            \restore_error_handler();
            \error_reporting($errorReporting);
        }

        $disableEntities = \libxml_disable_entity_loader();
        \libxml_disable_entity_loader($disableEntities);

        \libxml_disable_entity_loader($originalDisableEntities);

        $this->assertFalse($disableEntities);

        $file = vfsStream::newFile('valid.xml')->withContent(
            '<?xml version="1.0" encoding="UTF-8"?>
<root xmlns="http://example.com/schema">
</root>
            '
        )->at($this->root);
        $schemaFile = vfsStream::newFile('schema.xsd')->withContent(
            '<?xml version="1.0" encoding="UTF-8"?>

<xsd:schema xmlns="http://example.com/schema"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    targetNamespace="http://example.com/schema"
    elementFormDefault="qualified">

  <xsd:element name="root" />
</xsd:schema>
            '
        )->at($this->root);

        // should not throw an exception
        XmlUtils::loadFile($file->url(), $schemaFile->url());
    }
}

interface Validator
{
    public function validate();
}
