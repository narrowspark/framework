<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Command;

use Closure;
use ReflectionFunction;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClosureCommand extends AbstractCommand
{
    /**
     * Closure based command.
     *
     * @var \Closure
     */
    protected $callback;

    /**
     * Create a new command instance.
     *
     * @param string   $signature
     * @param \Closure $callback
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(string $signature, Closure $callback)
    {
        $this->callback  = $callback;
        $this->signature = $signature;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \ReflectionException
     * @throws \Invoker\Exception\InvocationException
     * @throws \Invoker\Exception\NotEnoughParametersException
     * @throws \Invoker\Exception\NotCallableException
     *
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $inputs     = \array_merge($input->getArguments(), $input->getOptions());
        $parameters = [];

        foreach ((new ReflectionFunction($this->callback))->getParameters() as $parameter) {
            if (isset($inputs[$parameter->name])) {
                $parameters[$parameter->name] = $inputs[$parameter->name];
            }
        }

        return $this->invoker->call(
            $this->callback->bindTo($this, $this),
            $parameters
        );
    }
}
