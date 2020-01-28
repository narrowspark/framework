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

namespace Viserio\Component\Cookie\Tests\Container\Provider\Compiled;

/**
 * This class has been auto-generated by Viserio Container Component.
 */
final class CookieServiceProviderContainer extends \Viserio\Component\Container\AbstractCompiledContainer
{
    /**
     * Create a new Compiled Container instance.
     */
    public function __construct()
    {
        $this->services = $this->privates = [];
        $this->methodMapping = [
            \Viserio\Contract\Cookie\QueueingFactory::class => 'get67da077da233cb8b970c6aa3e8c84e8e1efdd67257110f63df9e64b40d92c534',
            \Viserio\Component\OptionsResolver\Command\OptionDumpCommand::class => 'get5a73c93dbe469f9f1fae0210ee64ef2ab32ed536467d0570a89353766859bb62',
            \Viserio\Component\OptionsResolver\Command\OptionReaderCommand::class => 'get51bc2cdf2d87fcaa6a89ede54bc023ccfe784ddb4cc7a7e2be4ab3a7e9204471',
            'config' => 'get34bcaa5afa8745d92e6161e8495be3b939c5c6abb4dc2fd1f5a3cfdaba620256',
        ];
        $this->aliases = [
            \Viserio\Component\Cookie\CookieJar::class => \Viserio\Contract\Cookie\QueueingFactory::class,
            'cookie' => \Viserio\Contract\Cookie\QueueingFactory::class,
        ];
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

    /**
     * Returns the public Viserio\Contract\Cookie\QueueingFactory shared service.
     *
     * @return \Viserio\Component\Cookie\CookieJar
     */
    protected function get67da077da233cb8b970c6aa3e8c84e8e1efdd67257110f63df9e64b40d92c534(): \Viserio\Component\Cookie\CookieJar
    {
        $this->services[\Viserio\Contract\Cookie\QueueingFactory::class] = $instance = new \Viserio\Component\Cookie\CookieJar();

        $instance->setDefaultPathAndDomain('', '', true);

        return $instance;
    }

    /**
     * Returns the public Viserio\Component\Config\Command\OptionDumpCommand shared service.
     *
     * @return \Viserio\Component\Config\Command\OptionDumpCommand
     */
    protected function get5a73c93dbe469f9f1fae0210ee64ef2ab32ed536467d0570a89353766859bb62(): \Viserio\Component\OptionsResolver\Command\OptionDumpCommand
    {
        return $this->services[\Viserio\Component\OptionsResolver\Command\OptionDumpCommand::class] = new \Viserio\Component\OptionsResolver\Command\OptionDumpCommand();
    }

    /**
     * Returns the public Viserio\Component\Config\Command\OptionReaderCommand shared service.
     *
     * @return \Viserio\Component\Config\Command\OptionReaderCommand
     */
    protected function get51bc2cdf2d87fcaa6a89ede54bc023ccfe784ddb4cc7a7e2be4ab3a7e9204471(): \Viserio\Component\OptionsResolver\Command\OptionReaderCommand
    {
        return $this->services[\Viserio\Component\OptionsResolver\Command\OptionReaderCommand::class] = new \Viserio\Component\OptionsResolver\Command\OptionReaderCommand();
    }

    /**
     * Returns the public config service.
     *
     * @return array
     */
    protected function get34bcaa5afa8745d92e6161e8495be3b939c5c6abb4dc2fd1f5a3cfdaba620256(): array
    {
        return [
            'viserio' => [
                'cookie' => [
                    'domain' => '',
                    'path' => '',
                    'secure' => true,
                ],
            ],
        ];
    }
}
