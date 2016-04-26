<?php
namespace Viserio\Parsers;

use Exception;
use Viserio\Contracts\Parsers\TaggableParser as TaggableParserContract;

class TaggableParser extends Parser implements TaggableParserContract
{
    /**
     * Tagged key for grouping.
     *
     * @var string
     */
    private $taggedKey;

    /**
     * Set tag key.
     *
     * @param string $key
     */
    public function setTag($key)
    {
        $this->taggedKey = $key;

        return $this;
    }

    public function parse($payload)
    {
        if (!$this->taggedKey) {
            return parent::parse($payload);
        }

        return $this->group($this->taggedKey, parent::parse($payload));
    }

    /**
     * Check if config belongs to a group.
     *
     * @param string|array $taggedKey
     * @param array        $data
     *
     * @return array
     */
    protected function group($taggedKey, array $data)
    {
        $taggedData = [];

        foreach ($data as $key => $value) {
            $name = sprintf(
                '%s' . TaggableParserContract::TAG_DELIMITER . '%s',
                $taggedKey,
                $key
            );

            $taggedData[$name] = $value;
        }

        return $taggedData;
    }
}
