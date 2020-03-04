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

namespace Viserio\Component\Console\Command;

use Closure;
use ReflectionException;
use ReflectionFunction;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClosureCommand extends AbstractCommand
{
    /**
     * Closure based command.
     *
     * @var Closure
     */
    protected $callback;

    /**
     * Create a new command instance.
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(string $signature, Closure $callback)
    {
        $this->callback = $callback;
        $this->signature = $signature;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws ReflectionException
     * @throws \Invoker\Exception\InvocationException
     * @throws \Invoker\Exception\NotEnoughParametersException
     * @throws \Invoker\Exception\NotCallableException
     *
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputs = \array_merge($input->getArguments(), $input->getOptions());
        $parameters = [];

        foreach ((new ReflectionFunction($this->callback))->getParameters() as $parameter) {
            if (isset($inputs[$parameter->name])) {
                $parameters[$parameter->name] = $inputs[$parameter->name];
            }
        }

        return (int) $this->invoker->call(
            $this->callback->bindTo($this, $this),
            $parameters
        );
    }
}
