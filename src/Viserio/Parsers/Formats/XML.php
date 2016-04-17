<?php
namespace Viserio\Parsers\Formats;

use Exception;
use SimpleXMLElement;
use Viserio\Contracts\Parsers\Exception\ParseException;
use Viserio\Contracts\Parsers\Format as FormatContract;

class XML implements FormatContract
{
    /**
     * {@inheritdoc}
     */
    public function parse($payload)
    {
        try {
            $data = simplexml_load_file($payload);
            $data = json_decode(json_encode((array) $data), true); // Work around to accept xml input
            $data = str_replace(':{}', ':null', $data);
            $data = str_replace(':[]', ':null', $data);

            return $data;
        } catch (Exception $ex) {
            throw new ParseException([
                'message' => 'Failed To Parse XML'
            ]);
        }
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
                $this->arrayToXml($value, $subnode);
            } else {
                $key = is_numeric($key) ? sprintf('item%s', $key) : $key;
                $xml->addChild($key, $value);
            }
        }
    }
}
