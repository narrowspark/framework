<?php
declare(strict_types=1);
namespace Viserio\Component\Parser;

class TaggableParser extends Parser
{
    /**
     * Tag delimiter.
     *
     * @var string
     */
    public const TAG_DELIMITER = '::';

    /**
     * Key for tagging.
     *
     * @var string
     */
    private $tagKey;

    /**
     * Set tag key.
     *
     * @param string $key
     *
     * @return $this
     */
    public function setTag(string $key): self
    {
        $this->tagKey = $key;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        if (! $this->tagKey) {
            // @var $method self
            return parent::parse($payload);
        }

        return $this->tag($this->tagKey, parent::parse($payload));
    }

    /**
     * Tag all keys with given tag.
     *
     * @param string $tag
     * @param array  $data
     *
     * @return array
     */
    protected function tag(string $tag, array $data): array
    {
        $taggedData = [];

        foreach ($data as $key => $value) {
            $name = \sprintf(
                '%s' . self::TAG_DELIMITER . '%s',
                $tag,
                $key
            );

            $taggedData[$name] = $value;
        }

        return $taggedData;
    }
}
