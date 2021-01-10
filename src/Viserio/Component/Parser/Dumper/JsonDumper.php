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

namespace Viserio\Component\Parser\Dumper;

use JsonException;
use Viserio\Contract\Parser\Dumper as DumperContract;
use Viserio\Contract\Parser\Exception\DumpException;

class JsonDumper implements DumperContract
{
    /**
     * User specified recursion depth.
     *
     * @var int
     */
    private $depth = 512;

    /**
     * Bitmask of JSON decode options.
     *
     * @var int
     */
    private $options = \JSON_PRETTY_PRINT;

    /**
     * Set the user specified recursion depth.
     */
    public function setDepth(int $depth): void
    {
        $this->depth = $depth;
    }

    /**
     * Set the user specified recursion depth.
     */
    public function setOptions(int $options): void
    {
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        try {
            $json = (string) \json_encode($data, $this->options + \JSON_THROW_ON_ERROR, $this->depth);
        } catch (JsonException $exception) {
            throw new DumpException($exception->getMessage() . '.', $exception->getCode(), $exception);
        }

        return (string) \preg_replace('/\{\s+\}/', '{}', (string) \preg_replace('/\[\s+\]/', '[]', $json));
    }
}
