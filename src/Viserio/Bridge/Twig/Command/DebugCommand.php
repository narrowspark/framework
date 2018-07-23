<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Command;

use ReflectionFunction;
use ReflectionMethod;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use UnexpectedValueException;
use Viserio\Component\Console\Command\AbstractCommand;

/**
 * Lists twig functions, filters, globals and tests present in the current project.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @copyright Copyright (c) 2004-2017 Fabien Potencier
 */
class DebugCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'twig:debug';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'twig:debug
        [--filter= : Show details for all entries matching this filter.]
        [--format=txt : The output format. Supports `txt` or `json`.]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Shows a list of twig functions, filters, globals and tests';

    /**
     * A twig instance.
     *
     * @var \Twig\Environment
     */
    private $environment;

    /**
     * Create a DebugCommand instance.
     *
     * @param \Twig\Environment $environment
     */
    public function __construct(Environment $environment)
    {
        parent::__construct();

        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        $types = ['functions', 'filters', 'tests', 'globals'];

        if ($this->option('format') === 'json') {
            $data = [];

            foreach ($types as $type) {
                foreach ($this->environment->{'get' . \ucfirst($type)}() as $name => $entity) {
                    $data[$type][$name] = $this->getMetadata($type, $entity);
                }
            }

            $data['tests']        = \array_keys($data['tests']);
            $data['loader_paths'] = $this->getLoaderPaths($this->environment);

            $this->line(\json_encode($data));

            return 0;
        }

        $filter = $this->option('filter');

        foreach ($types as $index => $type) {
            $items = [];

            foreach ($this->environment->{'get' . \ucfirst($type)}() as $name => $entity) {
                if (! (bool) $filter || \mb_strpos($name, $filter) !== false) {
                    $items[$name] = $name . $this->getPrettyMetadata($type, $entity);
                }
            }

            if (\count($items) === 0) {
                continue;
            }

            $this->output->section(\ucfirst($type));

            \ksort($items);

            $this->output->listing($items);
        }

        $this->output->section('Configured Paths');
        $this->output->table(['Namespace', 'Paths'], $this->buildTableRows($this->getLoaderPaths($this->environment)));

        return 0;
    }

    /**
     * Get twig metadata.
     *
     * @param string $type
     * @param object $entity
     *
     * @throws \UnexpectedValueException
     * @throws \ReflectionException
     *
     * @return mixed
     */
    private function getMetadata(string $type, object $entity)
    {
        if ($type === 'globals') {
            return $entity;
        }

        if ($type === 'tests') {
            return null;
        }

        $isFilters = $type === 'filters';

        if ($type === 'functions' || $isFilters) {
            $cb = $entity->getCallable();

            if ($cb === null) {
                return null;
            }

            if (\is_array($cb)) {
                if (! \method_exists($cb[0], $cb[1])) {
                    return null;
                }

                $refl = new ReflectionMethod($cb[0], $cb[1]);
            } elseif (\is_object($cb) && \method_exists($cb, '__invoke')) {
                $refl = new ReflectionMethod($cb, '__invoke');
            } elseif (\function_exists($cb)) {
                $refl = new ReflectionFunction($cb);
            } elseif (\is_string($cb) && \preg_match('{^(.+)::(.+)$}', $cb, $m) && \method_exists($m[1], $m[2])) {
                $refl = new ReflectionMethod($m[1], $m[2]);
            } else {
                throw new UnexpectedValueException('Unsupported callback type');
            }

            // filter out context/environment args
            $args = \array_filter($refl->getParameters(), function ($param) use ($entity) {
                if ($entity->needsContext() && $param->getName() === 'context') {
                    return false;
                }

                return ! $param->getClass() || $param->getClass()->getName() !== 'Environment';
            });

            // format args
            $args = \array_map(function ($param) {
                if ($param->isDefaultValueAvailable()) {
                    return $param->getName() . ' = ' . \json_encode($param->getDefaultValue());
                }

                return $param->getName();
            }, $args);

            if ($isFilters) {
                // remove the value the filter is applied on
                \array_shift($args);
            }

            return $args;
        }

        return null;
    }

    /**
     * Transform metadata.
     *
     * @param string $type
     * @param object $entity
     *
     * @return string
     */
    private function getPrettyMetadata(string $type, object $entity): string
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
            if (\is_object($meta)) {
                return ' = object(' . \get_class($meta) . ')';
            }

            return ' = ' . \mb_substr(@\json_encode($meta), 0, 50);
        }

        if ($type === 'functions') {
            return '(' . \implode(', ', $meta) . ')';
        }

        if ($type === 'filters') {
            return \is_array($meta) ? '(' . \implode(', ', $meta) . ')' : '';
        }

        return '';
    }

    /**
     * Get the loader paths.
     *
     * @param \Twig\Environment $twig
     *
     * @return array
     */
    private function getLoaderPaths(Environment $twig): array
    {
        /** @var \Twig\Loader\FilesystemLoader $loader */
        $loader = $twig->getLoader();

        if (! $loader instanceof FilesystemLoader) {
            return [];
        }

        $loaderPaths = [];

        foreach ($loader->getNamespaces() as $namespace) {
            $paths = $loader->getPaths($namespace);

            if (FilesystemLoader::MAIN_NAMESPACE === $namespace) {
                $namespace = '(None)';
            } else {
                $namespace = '@' . $namespace;
            }

            $loaderPaths[$namespace] = $paths;
        }

        return $loaderPaths;
    }

    /**
     * Build configured path table.
     *
     * @var array $loaderPaths
     *
     * @return array
     */
    private function buildTableRows(array $loaderPaths): array
    {
        $rows = [];
        $firstNamespace = true;
        $prevHasSeparator = false;

        foreach ($loaderPaths as $namespace => $paths) {
            if (!$firstNamespace && !$prevHasSeparator && \count($paths) > 1) {
                $rows[] = ['', ''];
            }

            $firstNamespace = false;

            foreach ($paths as $path) {
                $rows[] = [$namespace, '- ' . $path];
                $namespace = '';
            }

            if (\count($paths) > 1) {
                $rows[] = ['', ''];
                $prevHasSeparator = true;
            } else {
                $prevHasSeparator = false;
            }
        }

        if ($prevHasSeparator) {
            \array_pop($rows);
        }
        return $rows;
    }
}
