<?php
namespace Viserio\Filesystem\Parser;

use Viserio\Contracts\Filesystem\LoadingException;
use Viserio\Contracts\Filesystem\Parser as ParserContract;
use Viserio\Filesystem\Filesystem;
use Viserio\Filesystem\Parser\Traits\IsGroupTrait;

class Xml implements ParserContract
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
     * @param \Viserio\Filesystem\Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Loads a XML file and gets its' contents as an array.
     *
     * @param string      $filename
     * @param string|null $group
     *
     * @throws \Exception
     *
     * @return array|string|null
     */
    public function load($filename, $group = null)
    {
        if ($this->files->exists($filename)) {
            $data = simplexml_load_file($filename);
            $data = unserialize(serialize(json_decode(json_encode((array) $data), true)));

            if ($group !== null) {
                return $this->isGroup($group, (array) $data);
            }

            return $data;
        }

        throw new LoadingException('Unable to load config ' . $filename);
    }

    /**
     * Checking if file ist supported.
     *
     * @param string $filename
     *
     * @return bool
     */
    public function supports($filename)
    {
        return (bool) preg_match('#\.xml(\.dist)?$#', $filename);
    }

    /**
     * Format a xml file for saving.
     *
     * @param array $data data
     *
     * @return string|false data export
     */
    public function format(array $data)
    {
        // creating object of SimpleXMLElement
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><config></config>');

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
    private function arrayToXml($data, \SimpleXMLElement & $xml)
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
