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

namespace ContainerD7fc715f00866a89c5767f6bf10439fb876247e2459e2aa431ace706c6e00935;

/**
 * This class has been auto-generated by Viserio Container Component.
 */
final class SessionServiceProviderContainer extends \Viserio\Component\Container\AbstractCompiledContainer
{
    /**
     * List of target dirs.
     *
     * @var array
     */
    private $targetDirs = [];

    /**
     * Path to the container dir.
     *
     * @var string
     */
    private $containerDir;

    /**
     * Create a new Compiled Container instance.
     *
     * @param array  $buildParameters
     * @param string $containerDir
     *
     * @var string $containerDir
     */
    public function __construct(array $buildParameters = [], string $containerDir = __DIR__)
    {
        $this->services = $this->privates = [];
        $dir = $this->targetDirs[0] = \dirname($containerDir);

        for ($i = 1; $i <= 5; $i++) {
            $this->targetDirs[$i] = $dir = \dirname($dir);
        }

        $this->containerDir = $containerDir;
        $this->parameters = \array_merge([
            'viserio.container.dumper.inline_factories' => true,
            'viserio.container.dumper.inline_class_loader' => false,
        ], $buildParameters);
        $this->methodMapping = [
            \Viserio\Contract\Session\Store::class => 'get32ef88687b15979f29861973a95a1e536e56b7c4f1158fd70a7dcba6de01d10f',
            \Viserio\Component\Session\SessionManager::class => 'get52481cc96e9a2c067832307a659a70b11502275753789749d549914acd2d01bd',
            'config' => 'get34bcaa5afa8745d92e6161e8495be3b939c5c6abb4dc2fd1f5a3cfdaba620256',
        ];
        $this->aliases = [
            'session' => \Viserio\Component\Session\SessionManager::class,
            'session.store' => \Viserio\Contract\Session\Store::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRemovedIds(): array
    {
        return require $this->containerDir . '/removed-ids.php';
    }

    /**
     * Returns the public Viserio\Contract\Session\Store shared service.
     *
     * @return mixed An instance returned by \Viserio\Component\Container\Definition\ReferenceDefinition::getDriver()
     */
    protected function get32ef88687b15979f29861973a95a1e536e56b7c4f1158fd70a7dcba6de01d10f()
    {
        return $this->services[\Viserio\Contract\Session\Store::class] = ($this->services[\Viserio\Component\Session\SessionManager::class] ?? $this->get52481cc96e9a2c067832307a659a70b11502275753789749d549914acd2d01bd())->getDriver();
    }

    /**
     * Returns the public Viserio\Component\Session\SessionManager shared service.
     *
     * @return \Viserio\Component\Session\SessionManager
     */
    protected function get52481cc96e9a2c067832307a659a70b11502275753789749d549914acd2d01bd(): \Viserio\Component\Session\SessionManager
    {
        return $this->services[\Viserio\Component\Session\SessionManager::class] = new \Viserio\Component\Session\SessionManager([
            'viserio' => [
                'session' => [
                    'default' => 'file',
                    'env' => 'local',
                    'lifetime' => 3000,
                    'key_path' => ($this->targetDirs[1] . '/test_key'),
                    'drivers' => [
                        'file' => [
                            'path' => ($this->targetDirs[1] . '/session'),
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Returns the public config service.
     *
     * @return array
     */
    protected function get34bcaa5afa8745d92e6161e8495be3b939c5c6abb4dc2fd1f5a3cfdaba620256(): array
    {
        return [
            'viserio' => [
                'session' => [
                    'default' => 'file',
                    'env' => 'local',
                    'lifetime' => 3000,
                    'key_path' => ($this->targetDirs[1] . '/test_key'),
                    'drivers' => [
                        'file' => [
                            'path' => ($this->targetDirs[1] . '/session'),
                        ],
                    ],
                ],
            ],
        ];
    }
}
