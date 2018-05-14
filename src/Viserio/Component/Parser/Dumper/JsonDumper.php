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

namespace Viserio\Component\Parser\Dumper;

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
     *
     * @param int $depth
     *
     * @return void
     */
    public function setDepth(int $depth): void
    {
        $this->depth = $depth;
    }

    /**
     * Set the user specified recursion depth.
     *
     * @param int $options
     *
     * @return void
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
        // Clear json_last_error()
        \json_encode(null);

        $json = \json_encode($data, $this->options, $this->depth);

        if (\json_last_error() !== \JSON_ERROR_NONE) {
            throw new DumpException(\sprintf('JSON dumping failed: %s.', \json_last_error_msg()));
        }

        $json = \preg_replace('/\[\s+\]/', '[]', $json);

        return \preg_replace('/\{\s+\}/', '{}', $json);
    }
}
