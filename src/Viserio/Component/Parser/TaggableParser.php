<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

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
     * @var null|string
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
        if ($this->tagKey === null) {
            return parent::parse($payload);
        }

        return $this->tag($this->tagKey, parent::parse($payload));
    }

    /**
     * Tag all keys with given tag.
     *
     * @param string                    $tag
     * @param array<int|string, string> $data
     *
     * @return array<string, string>
     */
    protected function tag(string $tag, array $data): array
    {
        $taggedData = [];

        foreach ($data as $key => $value) {
            $name = \sprintf(
                '%s' . self::TAG_DELIMITER . '%s',
                $tag,
                (string) $key
            );

            $taggedData[$name] = $value;
        }

        return $taggedData;
    }
}
