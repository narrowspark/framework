<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Traits;

use Interop\Container\ContainerInterface;
use Mockery as Mock;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\OptionsResolver\OptionsResolver;
use Viserio\Component\OptionsResolver\Tests\Fixtures\ConfigurationTraitAndContainerAwareConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixtures\ConfigurationTraitAwareConfiguration;

class ConfigurationTraitTest extends MockeryTestCase
{
    public function testResolveOptionsResolverFromDataContainer()
    {
        $fixture       = new ConfigurationTraitAwareConfiguration();
        $defaultConfig = [
            // package name
            'connection' => [
                // container id
                'orm_default' => [
                    // mandatory params
                    'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                    'params'      => [
                        'host'     => 'localhost',
                        'port'     => '3306',
                        'user'     => 'username',
                        'password' => 'password',
                        'dbname'   => 'database',
                    ],
                ],
            ],
        ];
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('doctrine')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('doctrine')
            ->andReturn($defaultConfig);
        $data = $this->mock(ContainerInterface::class);
        $data->shouldReceive('has')
            ->with(OptionsResolver::class)
            ->andReturn(true);
        $data->shouldReceive('get')
            ->with(OptionsResolver::class)
            ->andReturn(new OptionsResolver());
        $data->shouldReceive('has')
            ->with(RepositoryContract::class)
            ->andReturn(true);
        $data->shouldReceive('get')
            ->with(RepositoryContract::class)
            ->andReturn($config);

        self::assertArrayHasKey('orm_default', $fixture->getOptions($data));
    }

    public function testResolveOptionsResolverFromContainer()
    {
        $fixture   = new ConfigurationTraitAndContainerAwareConfiguration();

        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(OptionsResolver::class)
            ->andReturn(true);
        $container->shouldReceive('get')
            ->with(OptionsResolver::class)
            ->andReturn(new OptionsResolver());

        $fixture->setContainer($container);

        $defaultConfig = [
            'doctrine' => [
                // package name
                'connection' => [
                    // container id
                    'orm_default' => [
                        // mandatory params
                        'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                        'params'      => [
                            'host'     => 'localhost',
                            'port'     => '3306',
                            'user'     => 'username',
                            'password' => 'password',
                            'dbname'   => 'database',
                        ],
                    ],
                ],
            ],
        ];

        self::assertArrayHasKey('orm_default', $fixture->getOptions($defaultConfig));
    }
}
