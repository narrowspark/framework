<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Commands;

use ReflectionFunction;
use ReflectionMethod;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Twig_Environment;
use UnexpectedValueException;
use Viserio\Component\Console\Command\Command;

/**
 * Lists twig functions, filters, globals and tests present in the current project.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
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
        $container = $this->getContainer();

        if (! $container->has(Twig_Environment::class)) {
            $this->error('The Twig environment needs to be set.');

            return;
        }

        $twig = $container->get(Twig_Environment::class);

        $types = ['functions', 'filters', 'tests', 'globals'];

        if ($this->input->getOption('format') === 'json') {
            $data = [];

            foreach ($types as $type) {
                foreach ($twig->{'get' . ucfirst($type)}() as $name => $entity) {
                    $data[$type][$name] = $this->getMetadata($type, $entity);
                }
            }

            $data['tests'] = array_keys($data['tests']);

            $this->line(json_encode($data));

            return 0;
        }

        $filter = $this->input->getArgument('filter');

        foreach ($types as $index => $type) {
            $items = [];

            foreach ($twig->{'get' . ucfirst($type)}() as $name => $entity) {
                if (! $filter || false !== mb_strpos($name, $filter)) {
                    $items[$name] = $name . $this->getPrettyMetadata($type, $entity);
                }
            }

            if (empty($items)) {
                continue;
            }

            $this->output->section(ucfirst($type));

            ksort($items);

            $this->output->listing($items);
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function getArguments(): array
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
    protected function getOptions(): array
    {
        return [
            [
                'format',
                null,
                InputOption::VALUE_REQUIRED,
                'The output format (text or json)',
                'text',
            ],
        ];
    }

    /**
     * Get twig metadata.
     *
     * @param string $type
     * @param object $entity
     *
     * @return mixed
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
