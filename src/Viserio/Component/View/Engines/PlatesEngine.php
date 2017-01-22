<?php
declare(strict_types=1);
namespace Viserio\Component\View\Engines;

use Exception;
use League\Plates\Engine as LeagueEngine;
use League\Plates\Template\Template;
use RuntimeException;

class PlatesEngine extends AbstractBaseEngine
{
    /**
     * Engine instance.
     *
     * @var \League\Plates\Engine
     */
    protected $engine;

    /**
     * {@inheritdoc}
     */
    public function mandatoryOptions(): iterable
    {
        return array_merge(
            parent::mandatoryOptions(),
            [
                'engines' => [
                    'plates' => [
                        'file_extension',
                    ],
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $fileInfo, array $data = []): string
    {
        $engine = $this->getLoader();
        $config = $this->config['engines']['plates'];
        $engine = $this->loadExtension($engine, $config['extensions'] ?? []);

        if (! $engine->exists($fileInfo['name'])) {
            throw new Exception(sprintf('Template [%s] dont exist!', $fileInfo['name']));
        }

        return $engine->render($fileInfo['name'], $data);
    }

    /**
     * Plates paths loader.
     */
    protected function getLoader(): LeagueEngine
    {
        if (! $this->engine) {
            $config = $this->config;
            $paths  = $config['paths'];

            $this->engine = new LeagueEngine(
                // First value is the default folder
                array_values($paths)[0],
                $config['engines']['plates']['file_extension']
            );

            $paths = array_shift($paths);

            if (! empty($paths) && is_array($paths)) {
                foreach ($paths as $name => $path) {
                    if (is_array($path)) {
                        $this->engine->addFolder($name, $path[0], $path[1]);
                    } else {
                        $this->engine->addFolder($name, $path);
                    }
                }
            }
        }

        return $this->engine;
    }

    /**
     * load extension for plates.
     *
     * @param \League\Plates\Engine $engine
     * @param array|null            $exceptions
     *
     * @throws \RuntimeException
     *
     * @return \League\Plates\Engine
     */
    private function loadExtension(LeagueEngine $engine, ?array $exceptions): LeagueEngine
    {
        if (! empty($exceptions) && is_array($exceptions)) {
            foreach ($exceptions as $extension) {
                if (is_object($extension)) {
                    $engine->loadExtension($extension);
                } else {
                    throw new RuntimeException(sprintf(
                        'Plates extension [%s] is not a object.',
                        (string) $extension
                    ));
                }
            }
        }

        return $engine;
    }
}
