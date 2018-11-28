<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing\Commands;

use Viserio\Bridge\Doctrine\ORM\Commands\AbstractDoctrineCommand;

class LoadDataFixturesDoctrineCommand extends AbstractDoctrineCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'doctrine:testing:fixtures:load';
}
