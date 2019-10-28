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

namespace Viserio\Component\OptionsResolver\Container\Definition\Traits;

trait DimensionsTrait
{
    /**
     * Array of dimension names.
     *
     * @internal
     *
     * @var array
     */
    public static $classDimensions = [];

    /**
     * Array of dimension names.
     *
     * @var array
     */
    protected $dimensions = [];

    /**
     * Return the options aware class.
     *
     * @return array
     */
    public function getClassDimensions(): array
    {
        return $this->dimensions;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return self::$classDimensions;
    }
}
