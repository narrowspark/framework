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

namespace Viserio\Component\OptionsResolver\Container\Definition;

final class OptionDefinition extends AbstractOptionDefinition
{
    /**
     * The parameter key.
     *
     * @var string
     */
    private $key;

    /**
     * Create a new OptionDefinition instance.
     *
     * @param string      $key
     * @param string      $configClass
     * @param null|string $configId
     */
    public function __construct(string $key, string $configClass, string $configId = null)
    {
        $this->key = $key;

        parent::__construct($configClass, $configId);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->key;
    }
}
