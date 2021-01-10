<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Container\Pipeline;

use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Pipe as PipeContract;

class UnusedTagsPipe implements PipeContract
{
    /**
     * List of tags that should be skipped.
     *
     * @var array
     */
    private $whitelist;

    /**
     * Create a new UnusedTagsPipe instance.
     */
    public function __construct(array $whitelist)
    {
        $this->whitelist = $whitelist;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        $tags = \array_unique(\array_merge($containerBuilder->getTags(), $this->whitelist));

        foreach ($containerBuilder->getUnusedTags() as $tag) {
            // skip whitelisted tags
            if (\in_array($tag, $this->whitelist, true)) {
                continue;
            }

            // check for typos
            $candidates = [];

            foreach ($tags as $definedTag) {
                if ($definedTag === $tag) {
                    continue;
                }

                if (\strpos($definedTag, $tag) !== false || \levenshtein($tag, $definedTag) <= \strlen($tag) / 3) {
                    $candidates[] = $definedTag;
                }
            }

            $definitionTags = \array_keys(\iterator_to_array($containerBuilder->getTagged($tag)));

            $message = \sprintf('Tag [%s] was defined on service%s ["%s"], but was never used.', $tag, \count($definitionTags) !== 1 ? 's' : '', \implode('", "', $definitionTags));

            if (\count($candidates) !== 0) {
                $message .= \sprintf(' Did you mean [%s]?', \implode('", "', $candidates));
            }

            $containerBuilder->log($this, $message);
        }
    }
}
