<?php
declare(strict_types=1);
namespace Viserio\Component\Parser;

class GroupParser extends Parser
{
    /**
     * Key for grouping.
     *
     * @var string
     */
    private $groupKey;

    /**
     * Set group key.
     *
     * @param string $key
     *
     * @return $this
     */
    public function setGroup(string $key): self
    {
        $this->groupKey = $key;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        if (! $this->groupKey) {
            // @var $method self
            return parent::parse($payload);
        }

        return [$this->groupKey => parent::parse($payload)];
    }
}
