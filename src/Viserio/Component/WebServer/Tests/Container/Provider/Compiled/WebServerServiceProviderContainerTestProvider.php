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

namespace Viserio\Component\WebServer\Tests\Container\Provider\Compiled;

use Closure;

/**
 * This class has been auto-generated by Viserio Container Component.
 */
final class WebServerServiceProviderContainerTestProvider extends \Viserio\Component\Container\AbstractCompiledContainer
{
    /**
     * Create a new Compiled Container instance.
     */
    public function __construct()
    {
        $this->services = $this->privates = [];
        $this->methodMapping = [
            \Symfony\Component\Console\CommandLoader\CommandLoaderInterface::class => 'getce817e8bdc75399a693ba45b876c457a0f7fd422258f7d4eabc553987c2fbd31',
            \Symfony\Component\VarDumper\Cloner\ClonerInterface::class => 'get46b8e88975048cb31b0f0045017412a8f46d2a70cdb54dc5b7c742c769237ba0',
            \Symfony\Component\VarDumper\Dumper\CliDumper::class => 'getb25ea094617df466daf029d30df67a1587315f20d8e1949c7394923e2b8c1a3e',
            \Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface::class => 'get02975bf11e2d51ce3abc1bb4691867aaf6f2cc3c9d65d4c91e56cb424c700779',
            \Symfony\Component\VarDumper\Dumper\DataDumperInterface::class => 'get52ff2f4e302a18ec78bae676cc3bd87ac7a5c05257e9e0d8be9be9260311b56f',
            \Symfony\Component\VarDumper\Server\Connection::class => 'get0f0f10fc7b3f8319f5cf8a98aadb5af542049e35649bc462db731c9aced97636',
            \Symfony\Component\VarDumper\Server\DumpServer::class => 'getfda748f0bbfb99bdc062ba1f2e1a53453816c159bd32e4e93ecb76ea7db75cad',
            \Symfony\Component\VarDumper\VarDumper::class => 'get4b39bfcd6c1615eaf94a5811a808827fa131f2a8ee3ca76bbc73511c8ac261e3',
            \Viserio\Bridge\Twig\Extension\DumpExtension::class => 'get5e982a25346139e718d5327f75105fc4500493b38c9497926f65dcf3d033df69',
            \Viserio\Component\Console\Application::class => 'get206058a713a7172158e11c9d996f6a067c294ab0356ae6697060f162e057445a',
            \Viserio\Component\Config\Command\ConfigDumpCommand::class => 'get5a73c93dbe469f9f1fae0210ee64ef2ab32ed536467d0570a89353766859bb62',
            \Viserio\Component\Config\Command\ConfigReaderCommand::class => 'get51bc2cdf2d87fcaa6a89ede54bc023ccfe784ddb4cc7a7e2be4ab3a7e9204471',
            \Viserio\Component\WebServer\Command\ServerDumpCommand::class => 'get00ecdb07e8470c27b588865468b9d68a6a055b62608eec25ab3f01ac0bf823c8',
            \Viserio\Component\WebServer\Command\ServerLogCommand::class => 'get6634260609a498029e937e6580d7a26c22c24e9f0eba9a1d49076e43c5847b98',
            \Viserio\Component\WebServer\Command\ServerServeCommand::class => 'getc8c0b52951027d7f408639ef7023aea3fb91821b4feb5173c77c4be1f88712eb',
            \Viserio\Component\WebServer\Command\ServerStartCommand::class => 'getaa1bd3bdbd715302151e7666cc9b42505c8e9f1dcf97fc9560055813e05919b1',
            \Viserio\Component\WebServer\Command\ServerStatusCommand::class => 'get2c32e3887a07613412e74b743a822baf816caa6522db9dc9f2981dd55f6f2b74',
            \Viserio\Component\WebServer\Command\ServerStopCommand::class => 'get754cb50644e2488ce07a06cf7ed4301bb928539b70a9c2c84c8ec32d0805221e',
            \Viserio\Component\WebServer\Event\DumpListenerEvent::class => 'get32118e5e098ef34371a284a13485a5dc9b36750c2d0868068ca1c6bacaa09552',
            'config' => 'get34bcaa5afa8745d92e6161e8495be3b939c5c6abb4dc2fd1f5a3cfdaba620256',
            'console.command.ids' => 'getdbce155f9c0e95dbd4bfbfaadab27eb79915789fa80c6c65068ccf60c9ef9e18',
        ];
        $this->aliases = [
            \Symfony\Component\Console\Application::class => \Viserio\Component\Console\Application::class,
            \Symfony\Component\VarDumper\Cloner\VarCloner::class => \Symfony\Component\VarDumper\Cloner\ClonerInterface::class,
            \Symfony\Component\VarDumper\Dumper\HtmlDumper::class => \Symfony\Component\VarDumper\Dumper\DataDumperInterface::class,
            \Viserio\Component\WebServer\RequestContextProvider::class => \Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface::class,
            \Viserio\Provider\Debug\HtmlDumper::class => \Symfony\Component\VarDumper\Dumper\DataDumperInterface::class,
            'cerebro' => \Viserio\Component\Console\Application::class,
            'console' => \Viserio\Component\Console\Application::class,
        ];
        $this->syntheticIds = [
            \Psr\Http\Message\ServerRequestInterface::class => true,
            \Psr\Log\LoggerInterface::class => true,
            \Viserio\Contract\Console\Kernel::class => true,
        ];
    }

    /**
     * Returns the public Symfony\Component\Console\CommandLoader\CommandLoaderInterface shared service.
     *
     * @return \Viserio\Component\Console\CommandLoader\IteratorCommandLoader
     */
    protected function getce817e8bdc75399a693ba45b876c457a0f7fd422258f7d4eabc553987c2fbd31(): \Viserio\Component\Console\CommandLoader\IteratorCommandLoader
    {
        return $this->services[\Symfony\Component\Console\CommandLoader\CommandLoaderInterface::class] = new \Viserio\Component\Console\CommandLoader\IteratorCommandLoader(new \Viserio\Component\Container\RewindableGenerator(function () {
            yield 'option:dump' => ($this->services[\Viserio\Component\Config\Command\ConfigDumpCommand::class] ?? $this->get5a73c93dbe469f9f1fae0210ee64ef2ab32ed536467d0570a89353766859bb62());

            yield 'option:read' => ($this->services[\Viserio\Component\Config\Command\ConfigReaderCommand::class] ?? $this->get51bc2cdf2d87fcaa6a89ede54bc023ccfe784ddb4cc7a7e2be4ab3a7e9204471());

            yield 'server:log' => ($this->services[\Viserio\Component\WebServer\Command\ServerLogCommand::class] ?? $this->get6634260609a498029e937e6580d7a26c22c24e9f0eba9a1d49076e43c5847b98());

            yield 'server:dump' => ($this->services[\Viserio\Component\WebServer\Command\ServerDumpCommand::class] ?? $this->get00ecdb07e8470c27b588865468b9d68a6a055b62608eec25ab3f01ac0bf823c8());

            yield 'server:status' => ($this->services[\Viserio\Component\WebServer\Command\ServerStatusCommand::class] ?? $this->get2c32e3887a07613412e74b743a822baf816caa6522db9dc9f2981dd55f6f2b74());

            yield 'server:stop' => ($this->services[\Viserio\Component\WebServer\Command\ServerStopCommand::class] ?? $this->get754cb50644e2488ce07a06cf7ed4301bb928539b70a9c2c84c8ec32d0805221e());

            yield 'server:serve' => ($this->services[\Viserio\Component\WebServer\Command\ServerServeCommand::class] ?? $this->getc8c0b52951027d7f408639ef7023aea3fb91821b4feb5173c77c4be1f88712eb());

            yield 'server:start' => ($this->services[\Viserio\Component\WebServer\Command\ServerStartCommand::class] ?? $this->getaa1bd3bdbd715302151e7666cc9b42505c8e9f1dcf97fc9560055813e05919b1());
        }, 8));
    }

    /**
     * Returns the public Symfony\Component\VarDumper\Cloner\ClonerInterface shared service.
     *
     * @return \Symfony\Component\VarDumper\Cloner\VarCloner
     */
    protected function get46b8e88975048cb31b0f0045017412a8f46d2a70cdb54dc5b7c742c769237ba0(): \Symfony\Component\VarDumper\Cloner\VarCloner
    {
        $this->services[\Symfony\Component\VarDumper\Cloner\ClonerInterface::class] = $instance = new \Symfony\Component\VarDumper\Cloner\VarCloner();

        $instance->setMaxItems(2500);
        $instance->setMinDepth(1);
        $instance->setMaxString(-1);
        $instance->addCasters([
            Closure::class => 'Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster::unsetClosureFileInfo',
        ]);

        return $instance;
    }

    /**
     * Returns the public Symfony\Component\VarDumper\Dumper\CliDumper shared service.
     *
     * @return \Symfony\Component\VarDumper\Dumper\CliDumper
     */
    protected function getb25ea094617df466daf029d30df67a1587315f20d8e1949c7394923e2b8c1a3e(): \Symfony\Component\VarDumper\Dumper\CliDumper
    {
        return $this->services[\Symfony\Component\VarDumper\Dumper\CliDumper::class] = new \Symfony\Component\VarDumper\Dumper\CliDumper();
    }

    /**
     * Returns the public Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface shared service.
     *
     * @return \Viserio\Component\WebServer\RequestContextProvider
     */
    protected function get02975bf11e2d51ce3abc1bb4691867aaf6f2cc3c9d65d4c91e56cb424c700779(): \Viserio\Component\WebServer\RequestContextProvider
    {
        return $this->services[\Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface::class] = new \Viserio\Component\WebServer\RequestContextProvider(($this->services[\Psr\Http\Message\ServerRequestInterface::class] ?? $this->get(\Psr\Http\Message\ServerRequestInterface::class)));
    }

    /**
     * Returns the public Symfony\Component\VarDumper\Dumper\DataDumperInterface shared service.
     *
     * @return \Viserio\Provider\Debug\HtmlDumper
     */
    protected function get52ff2f4e302a18ec78bae676cc3bd87ac7a5c05257e9e0d8be9be9260311b56f(): \Viserio\Provider\Debug\HtmlDumper
    {
        $this->services[\Symfony\Component\VarDumper\Dumper\DataDumperInterface::class] = $instance = new \Viserio\Provider\Debug\HtmlDumper();

        $instance->addTheme('narrowspark', [
            'default' => 'color:#ffffff; line-height:normal; font:12px "Inconsolata", "Fira Mono", "Source Code Pro", Monaco, Consolas, "Lucida Console", monospace !important; word-wrap: break-word; white-space: pre-wrap; position:relative; z-index:99999; word-break: break-word',
            'num' => 'color:#bcd42a',
            'const' => 'color:#4bb1b1;',
            'str' => 'color:#bcd42a',
            'note' => 'color:#ef7c61',
            'ref' => 'color:#a0a0a0',
            'public' => 'color:#ffffff',
            'protected' => 'color:#ffffff',
            'private' => 'color:#ffffff',
            'meta' => 'color:#ffffff',
            'key' => 'color:#bcd42a',
            'index' => 'color:#ef7c61',
        ]);
        $instance->setTheme('narrowspark');

        return $instance;
    }

    /**
     * Returns the public Symfony\Component\VarDumper\Server\Connection shared service.
     *
     * @return \Symfony\Component\VarDumper\Server\Connection
     */
    protected function get0f0f10fc7b3f8319f5cf8a98aadb5af542049e35649bc462db731c9aced97636(): \Symfony\Component\VarDumper\Server\Connection
    {
        return $this->services[\Symfony\Component\VarDumper\Server\Connection::class] = new \Symfony\Component\VarDumper\Server\Connection('tcp://127.0.0.1:9912', [
            'request' => ($this->services[\Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface::class] ?? $this->get02975bf11e2d51ce3abc1bb4691867aaf6f2cc3c9d65d4c91e56cb424c700779()),
        ]);
    }

    /**
     * Returns the public Symfony\Component\VarDumper\Server\DumpServer shared service.
     *
     * @return \Symfony\Component\VarDumper\Server\DumpServer
     */
    protected function getfda748f0bbfb99bdc062ba1f2e1a53453816c159bd32e4e93ecb76ea7db75cad(): \Symfony\Component\VarDumper\Server\DumpServer
    {
        return $this->services[\Symfony\Component\VarDumper\Server\DumpServer::class] = new \Symfony\Component\VarDumper\Server\DumpServer('tcp://127.0.0.1:9912', ($this->services[\Psr\Log\LoggerInterface::class] ?? $this->get(\Psr\Log\LoggerInterface::class)));
    }

    /**
     * Returns the public Symfony\Component\VarDumper\VarDumper shared service.
     *
     * @return \Symfony\Component\VarDumper\VarDumper
     */
    protected function get4b39bfcd6c1615eaf94a5811a808827fa131f2a8ee3ca76bbc73511c8ac261e3(): \Symfony\Component\VarDumper\VarDumper
    {
        return $this->services[\Symfony\Component\VarDumper\VarDumper::class] = new \Symfony\Component\VarDumper\VarDumper();
    }

    /**
     * Returns the public Viserio\Bridge\Twig\Extension\DumpExtension shared service.
     *
     * @return \Viserio\Bridge\Twig\Extension\DumpExtension
     */
    protected function get5e982a25346139e718d5327f75105fc4500493b38c9497926f65dcf3d033df69(): \Viserio\Bridge\Twig\Extension\DumpExtension
    {
        return $this->services[\Viserio\Bridge\Twig\Extension\DumpExtension::class] = new \Viserio\Bridge\Twig\Extension\DumpExtension(($this->services[\Symfony\Component\VarDumper\Cloner\ClonerInterface::class] ?? $this->get46b8e88975048cb31b0f0045017412a8f46d2a70cdb54dc5b7c742c769237ba0()), ($this->services[\Symfony\Component\VarDumper\Dumper\DataDumperInterface::class] ?? $this->get52ff2f4e302a18ec78bae676cc3bd87ac7a5c05257e9e0d8be9be9260311b56f()));
    }

    /**
     * Returns the public Viserio\Component\Console\Application shared service.
     *
     * @return \Viserio\Component\Console\Application
     */
    protected function get206058a713a7172158e11c9d996f6a067c294ab0356ae6697060f162e057445a(): \Viserio\Component\Console\Application
    {
        $this->services[\Viserio\Component\Console\Application::class] = $instance = new \Viserio\Component\Console\Application();

        $instance->setContainer($this);

        if ($this->has(\Symfony\Component\Console\CommandLoader\CommandLoaderInterface::class)) {
            $instance->setCommandLoader(($this->services[\Symfony\Component\Console\CommandLoader\CommandLoaderInterface::class] ?? $this->getce817e8bdc75399a693ba45b876c457a0f7fd422258f7d4eabc553987c2fbd31()));
        }

        return $instance;
    }

    /**
     * Returns the public Viserio\Component\Config\Command\ConfigDumpCommand shared service.
     *
     * @return \Viserio\Component\Config\Command\ConfigDumpCommand
     */
    protected function get5a73c93dbe469f9f1fae0210ee64ef2ab32ed536467d0570a89353766859bb62(): \Viserio\Component\Config\Command\ConfigDumpCommand
    {
        $this->services[\Viserio\Component\Config\Command\ConfigDumpCommand::class] = $instance = new \Viserio\Component\Config\Command\ConfigDumpCommand();

        $instance->setName('option:dump');

        return $instance;
    }

    /**
     * Returns the public Viserio\Component\Config\Command\ConfigReaderCommand shared service.
     *
     * @return \Viserio\Component\Config\Command\ConfigReaderCommand
     */
    protected function get51bc2cdf2d87fcaa6a89ede54bc023ccfe784ddb4cc7a7e2be4ab3a7e9204471(): \Viserio\Component\Config\Command\ConfigReaderCommand
    {
        $this->services[\Viserio\Component\Config\Command\ConfigReaderCommand::class] = $instance = new \Viserio\Component\Config\Command\ConfigReaderCommand();

        $instance->setName('option:read');

        return $instance;
    }

    /**
     * Returns the public Viserio\Component\WebServer\Command\ServerDumpCommand shared service.
     *
     * @return \Viserio\Component\WebServer\Command\ServerDumpCommand
     */
    protected function get00ecdb07e8470c27b588865468b9d68a6a055b62608eec25ab3f01ac0bf823c8(): \Viserio\Component\WebServer\Command\ServerDumpCommand
    {
        $this->services[\Viserio\Component\WebServer\Command\ServerDumpCommand::class] = $instance = new \Viserio\Component\WebServer\Command\ServerDumpCommand(($this->services[\Symfony\Component\VarDumper\Server\DumpServer::class] ?? $this->getfda748f0bbfb99bdc062ba1f2e1a53453816c159bd32e4e93ecb76ea7db75cad()), ($this->services[\Symfony\Component\VarDumper\Dumper\CliDumper::class] ?? $this->getb25ea094617df466daf029d30df67a1587315f20d8e1949c7394923e2b8c1a3e()), ($this->services[\Symfony\Component\VarDumper\Dumper\DataDumperInterface::class] ?? $this->get52ff2f4e302a18ec78bae676cc3bd87ac7a5c05257e9e0d8be9be9260311b56f()));

        $instance->setName('server:dump');

        return $instance;
    }

    /**
     * Returns the public Viserio\Component\WebServer\Command\ServerLogCommand shared service.
     *
     * @return \Viserio\Component\WebServer\Command\ServerLogCommand
     */
    protected function get6634260609a498029e937e6580d7a26c22c24e9f0eba9a1d49076e43c5847b98(): \Viserio\Component\WebServer\Command\ServerLogCommand
    {
        $this->services[\Viserio\Component\WebServer\Command\ServerLogCommand::class] = $instance = new \Viserio\Component\WebServer\Command\ServerLogCommand();

        $instance->setName('server:log');

        return $instance;
    }

    /**
     * Returns the public Viserio\Component\WebServer\Command\ServerServeCommand shared service.
     *
     * @return \Viserio\Component\WebServer\Command\ServerServeCommand
     */
    protected function getc8c0b52951027d7f408639ef7023aea3fb91821b4feb5173c77c4be1f88712eb(): \Viserio\Component\WebServer\Command\ServerServeCommand
    {
        $a = ($this->services[\Viserio\Contract\Console\Kernel::class] ?? $this->get(\Viserio\Contract\Console\Kernel::class));

        $this->services[\Viserio\Component\WebServer\Command\ServerServeCommand::class] = $instance = new \Viserio\Component\WebServer\Command\ServerServeCommand($a->getPublicPath(), $a->getEnvironment());

        $instance->setName('server:serve');

        return $instance;
    }

    /**
     * Returns the public Viserio\Component\WebServer\Command\ServerStartCommand shared service.
     *
     * @return \Viserio\Component\WebServer\Command\ServerStartCommand
     */
    protected function getaa1bd3bdbd715302151e7666cc9b42505c8e9f1dcf97fc9560055813e05919b1(): \Viserio\Component\WebServer\Command\ServerStartCommand
    {
        $a = ($this->services[\Viserio\Contract\Console\Kernel::class] ?? $this->get(\Viserio\Contract\Console\Kernel::class));

        $this->services[\Viserio\Component\WebServer\Command\ServerStartCommand::class] = $instance = new \Viserio\Component\WebServer\Command\ServerStartCommand($a->getPublicPath(), $a->getEnvironment());

        $instance->setName('server:start');

        return $instance;
    }

    /**
     * Returns the public Viserio\Component\WebServer\Command\ServerStatusCommand shared service.
     *
     * @return \Viserio\Component\WebServer\Command\ServerStatusCommand
     */
    protected function get2c32e3887a07613412e74b743a822baf816caa6522db9dc9f2981dd55f6f2b74(): \Viserio\Component\WebServer\Command\ServerStatusCommand
    {
        $this->services[\Viserio\Component\WebServer\Command\ServerStatusCommand::class] = $instance = new \Viserio\Component\WebServer\Command\ServerStatusCommand();

        $instance->setName('server:status');

        return $instance;
    }

    /**
     * Returns the public Viserio\Component\WebServer\Command\ServerStopCommand shared service.
     *
     * @return \Viserio\Component\WebServer\Command\ServerStopCommand
     */
    protected function get754cb50644e2488ce07a06cf7ed4301bb928539b70a9c2c84c8ec32d0805221e(): \Viserio\Component\WebServer\Command\ServerStopCommand
    {
        $this->services[\Viserio\Component\WebServer\Command\ServerStopCommand::class] = $instance = new \Viserio\Component\WebServer\Command\ServerStopCommand();

        $instance->setName('server:stop');

        return $instance;
    }

    /**
     * Returns the public Viserio\Component\WebServer\Event\DumpListenerEvent shared service.
     *
     * @return \Viserio\Component\WebServer\Event\DumpListenerEvent
     */
    protected function get32118e5e098ef34371a284a13485a5dc9b36750c2d0868068ca1c6bacaa09552(): \Viserio\Component\WebServer\Event\DumpListenerEvent
    {
        return $this->services[\Viserio\Component\WebServer\Event\DumpListenerEvent::class] = new \Viserio\Component\WebServer\Event\DumpListenerEvent(($this->services[\Symfony\Component\VarDumper\Cloner\ClonerInterface::class] ?? $this->get46b8e88975048cb31b0f0045017412a8f46d2a70cdb54dc5b7c742c769237ba0()), ($this->services[\Symfony\Component\VarDumper\Dumper\DataDumperInterface::class] ?? $this->get52ff2f4e302a18ec78bae676cc3bd87ac7a5c05257e9e0d8be9be9260311b56f()), ($this->services[\Symfony\Component\VarDumper\Server\Connection::class] ?? $this->get0f0f10fc7b3f8319f5cf8a98aadb5af542049e35649bc462db731c9aced97636()));
    }

    /**
     * Returns the public config service.
     *
     * @return array
     */
    protected function get34bcaa5afa8745d92e6161e8495be3b939c5c6abb4dc2fd1f5a3cfdaba620256(): array
    {
        return [
            'viserio' => [],
        ];
    }

    /**
     * Returns the public console.command.ids service.
     *
     * @return array
     */
    protected function getdbce155f9c0e95dbd4bfbfaadab27eb79915789fa80c6c65068ccf60c9ef9e18(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getRemovedIds(): array
    {
        return [
            \Psr\Container\ContainerInterface::class => true,
            \Viserio\Contract\Container\Factory::class => true,
            \Viserio\Contract\Container\TaggedContainer::class => true,
            'container' => true,
        ];
    }
}
