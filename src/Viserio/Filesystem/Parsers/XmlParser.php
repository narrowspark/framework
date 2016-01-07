<?php
namespace Viserio\Filesystem\Parsers;

use League\Flysystem\FileNotFoundException;
use SimpleXMLElement;
use Viserio\Contracts\Filesystem\Exception\LoadingException;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Contracts\Filesystem\Parser as ParserContract;
use Viserio\Filesystem\Parsers\Traits\IsGroupTrait;

class XmlParser implements ParserContract
{
    use IsGroupTrait;

    /**
     * The filesystem instance.
     *
     * @var \Viserio\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new file filesystem loader.
     *
     * @param \Viserio\Contracts\Filesystem\Filesystem $files
     */
    public function __construct(FilesystemContract $files)
    {
        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($filename, $group = null)
    {
        if ($this->files->has($filename)) {
            $data = simplexml_load_file($filename);
            $data = unserialize(serialize(json_decode(json_encode((array) $data), true)));

            if ($group !== null) {
                return $this->isGroup($group, (array) $data);
            }

            return (array) $data;
        }

        throw new FileNotFoundException($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($filename)
    {
        return (bool) preg_match('/(\.xml)(\.dist)?/', $filename);
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data)
    {
        // creating object of SimpleXMLElement
        $xml = new SimpleXMLElement('<?xml version="1.0"?><config></config>');

        // function call to convert array to xml
        $this->arrayToXml($data, $xml);

        return $xml->asXML();
    }

    /**
     * Defination to convert array to xml [NOT IMPLEMENTED].
     *
     * @param array             $data data
     * @param \SimpleXMLElement $xml
     *
     * @return string|null
     */
    private function arrayToXml($data, SimpleXMLElement &$xml)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $key = is_numeric($key) ? sprintf('item%s', $key) : $key;
                $subnode = $xml->addChild($key);
                array_to_xml($value, $subnode);
            } else {
                $key = is_numeric($key) ? sprintf('item%s', $key) : $key;
                $xml->addChild($key, $value);
            }
        }
    }
}
