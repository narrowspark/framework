<?php
declare(strict_types=1);
namespace Viserio\Provider\Doctrine\Migration\Configuration;

use Doctrine\DBAL\Migrations\Configuration\Configuration as MigrationsConfiguration;
use Viserio\Provider\Doctrine\Contract\Migration\NamingStrategy as NamingStrategyContract;

class Configuration extends MigrationsConfiguration
{
    /**
     * A naming strategy instance.
     *
     * @var \Viserio\Provider\Doctrine\Contract\Migration\NamingStrategy
     */
    protected $namingStrategy;

    /**
     * Get a naming strategy.
     *
     * @return \Viserio\Provider\Doctrine\Contract\Migration\NamingStrategy
     */
    public function getNamingStrategy(): NamingStrategyContract
    {
        return $this->namingStrategy;
    }

    /**
     * Set a naming strategy object.
     *
     * @param \Viserio\Provider\Doctrine\Contract\Migration\NamingStrategy $namingStrategy
     *
     * @return void
     */
    public function setNamingStrategy(NamingStrategyContract $namingStrategy): void
    {
        $this->namingStrategy = $namingStrategy;
    }
}
