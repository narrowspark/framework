<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Migration\Configuration;

use Doctrine\DBAL\Migrations\Configuration\Configuration as MigrationsConfiguration;
use Viserio\Bridge\Doctrine\Contract\Migration\NamingStrategy as NamingStrategyContract;

class Configuration extends MigrationsConfiguration
{
    /**
     * A naming strategy instance.
     *
     * @var \Viserio\Bridge\Doctrine\Contract\Migration\NamingStrategy
     */
    protected $namingStrategy;

    /**
     * Set a naming strategy object.
     *
     * @param \Viserio\Bridge\Doctrine\Contract\Migration\NamingStrategy $namingStrategy
     *
     * @return void
     */
    public function setNamingStrategy(NamingStrategyContract $namingStrategy): void
    {
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * Get a naming strategy.
     *
     * @return \Viserio\Bridge\Doctrine\Contract\Migration\NamingStrategy
     */
    public function getNamingStrategy(): NamingStrategyContract
    {
        return $this->namingStrategy;
    }
}
