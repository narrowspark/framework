<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

class UniversalContainerIdConfiguration implements RequiresComponentConfigIdContract, ProvidesDefaultOptionsContract, RequiresMandatoryOptionsContract
{
    public const TYPE_ARRAY_ITERATOR = 0;
    public const TYPE_ARRAY_OBJECT   = 1;
    public const TYPE_ARRAY_ARRAY    = 2;
    public const TYPE_ONLY_ITERATOR  = 3;

    private static $dimensions = [
        'doctrine',
        'universal',
    ];

    private static $getMandatoryOptions = [
        'params' => ['user', 'dbname'],
        'driverClass',
    ];

    private static $getDefaultOptions = [
        'params' => [
            'host' => 'awesomehost',
            'port' => '4444',
        ],
    ];

    private $type;

    public function __construct(int $type)
    {
        $this->type = $type;
    }

    public function getDimensions(): iterable
    {
        return $this->getData('dimensions');
    }

    public function getMandatoryOptions(): iterable
    {
        return $this->getData('getMandatoryOptions');
    }

    public function getDefaultOptions(): iterable
    {
        return $this->getData('getDefaultOptions');
    }

    private function getData($name)
    {
        switch ($this->type) {
            case self::TYPE_ARRAY_ITERATOR:
                return new \ArrayIterator(self::$$name);
                break;
            case self::TYPE_ARRAY_OBJECT:
                return new \ArrayObject(self::$$name);
                break;
            case self::TYPE_ONLY_ITERATOR:
                return new OnlyIterator(self::$$name);
                break;
            case self::TYPE_ARRAY_ARRAY:
            default:
                return self::$$name;
                break;
        }
    }
}
