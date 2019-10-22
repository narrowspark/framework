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

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Viserio\Contract\Parser\Dumper as DumperContract;
use Viserio\Contract\Parser\Exception\DumpException;

class YamlDumper implements DumperContract
{
    /**
     * Create a new Yaml dumper.
     *
     * @throws \RuntimeException
     */
    public function __construct()
    {
        /** @codeCoverageIgnoreStart */
        if (! \class_exists(SymfonyYaml::class)) {
            throw new RuntimeException('Unable to read yaml as the Symfony Yaml Component is not installed.');
        }
        /** @codeCoverageIgnoreEnd */
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function dump(array $data): string
    {
        try {
            return SymfonyYaml::dump($data);
        } catch (InvalidArgumentException $exception) {
            throw new DumpException($exception->getMessage());
        }
    }
}
