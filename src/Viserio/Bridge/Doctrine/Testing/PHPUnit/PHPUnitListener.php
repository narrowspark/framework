<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing\DBAL\PHPUnit;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use \PHPUnit\Framework\TestSuite;

class PHPUnitListener
{
    use TestListenerDefaultImplementation;

    /**
     * {@inheritdoc}
     */
    public function startTest(Test $test): void
    {
        StaticDriver::beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function endTest(Test $test, $time): void
    {
        StaticDriver::rollBack();
    }

    /**
     * {@inheritdoc}
     */
    public function startTestSuite(TestSuite $suite): void
    {
        StaticDriver::setKeepStaticConnections(true);
    }
}
