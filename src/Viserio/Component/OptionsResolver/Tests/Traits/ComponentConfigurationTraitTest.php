<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Traits;

use Interop\Container\ContainerInterface;
use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\OptionsResolver\ComponentOptionsResolver;
use Viserio\Component\OptionsResolver\Tests\Fixtures\ComponentConfigurationTraitAndContainerAwareConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixtures\ComponentConfigurationTraitAwareConfiguration;

class ComponentConfigurationTraitTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testResolveComponentOptionsResolverFromDataContainer()
    {
        $fixture       = new ComponentConfigurationTraitAwareConfiguration();
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
            ->with(ComponentOptionsResolver::class)
            ->andReturn(true);
        $data->shouldReceive('get')
            ->with(ComponentOptionsResolver::class)
            ->andReturn(new ComponentOptionsResolver());
        $data->shouldReceive('has')
            ->with(RepositoryContract::class)
            ->andReturn(true);
        $data->shouldReceive('get')
            ->with(RepositoryContract::class)
            ->andReturn($config);

        self::assertArrayHasKey('orm_default', $fixture->getOptions($data));
    }

    public function testResolveComponentOptionsResolverFromContainer()
    {
        $fixture   = new ComponentConfigurationTraitAndContainerAwareConfiguration();

        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(ComponentOptionsResolver::class)
            ->andReturn(true);
        $container->shouldReceive('get')
            ->with(ComponentOptionsResolver::class)
            ->andReturn(new ComponentOptionsResolver());

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
