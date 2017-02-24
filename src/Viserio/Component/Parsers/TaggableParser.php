<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers;

use Viserio\Component\Contracts\Parsers\TaggableParser as TaggableParserContract;

class TaggableParser extends Parser implements TaggableParserContract
{
    /**
     * Tagged key for grouping.
     *
     * @var string
     */
    private $taggedKey;

    /**
     * {@inheritdoc}
     */
    public function setTag(string $key): TaggableParserContract
    {
        $this->taggedKey = $key;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        if (! $this->taggedKey) {
            /* @var $method self */
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
    protected function group($taggedKey, array $data): array
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
