<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Commands;

use ReflectionFunction;
use ReflectionMethod;
use UnexpectedValueException;
use Viserio\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DebugCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'twig:debug';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Shows a list of twig functions, filters, globals and tests';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return [
            [
                'filter',
                InputArgument::OPTIONAL,
                'Show details for all entries matching this filter.',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return [
            [
                'format',
                null,
                InputOption::VALUE_REQUIRED,
                'The output format (text or json)',
                'text'
            ],
        ];
    }

    /**
     * Get twig metadata.
     *
     * @param string $type
     * @param object $entity
     *
     * @return null|string|array
     */
    private function getMetadata(string $type, $entity)
    {
        if ($type === 'globals') {
            return $entity;
        }

        if ($type === 'tests') {
            return;
        }

        if ($type === 'functions' || $type === 'filters') {
            $cb = $entity->getCallable();

            if ($cb === null) {
                return;
            }

            if (is_array($cb)) {
                if (! method_exists($cb[0], $cb[1])) {
                    return;
                }

                $refl = new ReflectionMethod($cb[0], $cb[1]);
            } elseif (is_object($cb) && method_exists($cb, '__invoke')) {
                $refl = new ReflectionMethod($cb, '__invoke');
            } elseif (function_exists($cb)) {
                $refl = new ReflectionFunction($cb);
            } elseif (is_string($cb) && preg_match('{^(.+)::(.+)$}', $cb, $m) && method_exists($m[1], $m[2])) {
                $refl = new ReflectionMethod($m[1], $m[2]);
            } else {
                throw new UnexpectedValueException('Unsupported callback type');
            }

            // filter out context/environment args
            $args = array_filter($refl->getParameters(), function ($param) use ($entity) {
                if ($entity->needsContext() && $param->getName() === 'context') {
                    return false;
                }

                return ! $param->getClass() || $param->getClass()->getName() !== 'Twig_Environment';
            });

            // format args
            $args = array_map(function ($param) {
                if ($param->isDefaultValueAvailable()) {
                    return $param->getName() . ' = ' . json_encode($param->getDefaultValue());
                }

                return $param->getName();
            }, $args);

            if ($type === 'filters') {
                // remove the value the filter is applied on
                array_shift($args);
            }

            return $args;
        }
    }

    /**
     * Transform metadata.
     *
     * @param string $type
     * @param object $entity
     *
     * @return string
     */
    private function getPrettyMetadata(string $type, $entity): string
    {
        if ($type === 'tests') {
            return '';
        }

        try {
            $meta = $this->getMetadata($type, $entity);

            if ($meta === null) {
                return '(unknown?)';
            }
        } catch (UnexpectedValueException $e) {
            return ' <error>' . $e->getMessage() . '</error>';
        }

        if ($type === 'globals') {
            if (is_object($meta)) {
                return ' = object(' . get_class($meta) . ')';
            }

            return ' = ' . mb_substr(@json_encode($meta), 0, 50);
        }

        if ($type === 'functions') {
            return '(' . implode(', ', $meta) . ')';
        }

        if ($type === 'filters') {
            return $meta ? '(' . implode(', ', $meta) . ')' : '';
        }
    }
}
