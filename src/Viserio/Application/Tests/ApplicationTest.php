<?php
namespace Viserio\Application\Tests;

use Viserio\Application\Application;

/**
 * ApplicationTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetProviderByParentClass()
    {
        $app = new Application(['test', 'test2']);
        $app->register('ApplicationChildProviderStub');
        $this->assertEquals($app->getProvider('ApplicationChildProviderStub'), $app->getProvider('ApplicationParentProviderStub'));
        $this->assertEquals($app->getProvider('ApplicationChildProviderStub'), $app->getProvider('ApplicationInterfaceProviderStub'));
    }
}

class ApplicationParentProviderStub extends \Viserio\Application\ServiceProvider
{
    public function register()
    {
    }
}

interface ApplicationInterfaceProviderStub
{
}

class ApplicationChildProviderStub extends ApplicationParentProviderStub implements ApplicationInterfaceProviderStub
{
    public function register()
    {
    }
}
