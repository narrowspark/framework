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

namespace Viserio\Component\Container\Definition;

use Opis\Closure\ReflectionClosure;
use PhpParser\PrettyPrinter\Standard;
use Viserio\Component\Container\Definition\Traits\ArgumentAwareTrait;
use Viserio\Component\Container\Definition\Traits\AutowiredAwareTrait;
use Viserio\Component\Container\Definition\Traits\DecoratorAwareTrait;
use Viserio\Contract\Container\Definition\ClosureDefinition as ClosureDefinitionContract;
use Viserio\Contract\Support\Exception\MissingPackageException;

final class ClosureDefinition extends AbstractDefinition implements ClosureDefinitionContract
{
    use ArgumentAwareTrait;
    use DecoratorAwareTrait;
    use AutowiredAwareTrait;

    /**
     * Default deprecation template.
     *
     * @var string
     */
    protected $defaultDeprecationTemplate = 'The [%s] service is deprecated. You should stop using it, as it will be removed in the future.';

    /** @var bool */
    private $executable = false;

    /**
     * Create a new Closure Definition instance.
     *
     * @param callable $value
     *
     * @throws \Viserio\Contract\Support\Exception\MissingPackageException
     */
    public function __construct(string $name, $value, int $type)
    {
        parent::__construct($name, $type);

        if (! \class_exists(Standard::class) && ! class_exists(ReflectionClosure::class)) {
            throw new MissingPackageException(['nikic/php-parser'], self::class, ', closure dumping');
        }

        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function isExecutable(): bool
    {
        return $this->executable;
    }

    /**
     * {@inheritdoc}
     */
    public function setExecutable(bool $bool): ClosureDefinitionContract
    {
        $this->executable = $bool;

        return $this;
    }
}
