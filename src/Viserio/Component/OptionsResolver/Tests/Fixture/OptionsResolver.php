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

namespace Viserio\Component\OptionsResolver\Tests\Fixture;

use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class OptionsResolver
{
    use OptionsResolverTrait;

    protected static $configClass;

    protected static $data;

    /**
     * {@inheritdoc}
     */
    protected static function getConfigClass(): string
    {
        return self::$configClass;
    }

    /**
     * @param object             $configClass
     * @param array|\ArrayAccess $data
     *
     * @return self
     */
    public function configure(object $configClass, $data): self
    {
        self::$configClass = \get_class($configClass);
        self::$data = $data;

        return $this;
    }

    public function resolve(string $configId = null): array
    {
        return self::resolveOptions(self::$data, $configId);
    }
}
