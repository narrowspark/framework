<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\Fixture;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Profiler\DataCollectors\AbstractDataCollector;

class FixtureDataCollector extends AbstractDataCollector
{
    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
    }

    public function getTableDefault()
    {
        return $this->createTable(['test key' => 'test value'], ['name' => 'test']);
    }

    public function getTableArray()
    {
        return $this->createTable([['test key' => 'test value']], ['name' => 'array']);
    }

    public function getTooltippGroupDefault()
    {
        return $this->createTooltipGroup([
            'test' => 'test',
        ]);
    }

    public function getTooltippGroupDefaultWithLink()
    {
        return $this->createTooltipGroup([
            'Resources' => '<a href="//narrowspark.de/doc/">Read Narrowspark Doc\'s </a>',
            'Help'      => '<a href="//narrowspark.de/support">Narrowspark Support Channels</a>',
        ]);
    }

    public function getTooltippGroupArray()
    {
        return $this->createTooltipGroup([
            'test' => [
                [
                    'class' => 'test',
                    'value' => 'test',
                ],
                [
                    'class' => 'test2',
                    'value' => 'test2',
                ],
            ],
        ]);
    }

    public function getTabs()
    {
        return $this->createTabs([
            [
                'name'    => 'test',
                'content' => 'test',
            ],
        ]);
    }

    public function getDropdownMenuContent()
    {
        return $this->createDropdownMenuContent([
            'dropdown' => 'content',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon'  => '',
            'label' => '',
            'value' => '',
        ];
    }
}
