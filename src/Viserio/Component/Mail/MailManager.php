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

namespace Viserio\Component\Mail;

use ArrayAccess;
use Swift_DependencyContainer;
use Swift_Mailer;
use Swift_Transport;
use Viserio\Component\Manager\AbstractConnectionManager;
use Viserio\Component\Support\Str;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Events\Traits\EventManagerAwareTrait;
use Viserio\Contract\Mail\Mailer as MailerContract;
use Viserio\Contract\Manager\Exception\InvalidArgumentException;
use Viserio\Contract\Queue\QueueConnector as QueueContract;
use Viserio\Contract\View\Traits\ViewAwareTrait;

class MailManager extends AbstractConnectionManager implements ProvidesDefaultConfigContract
{
    use EventManagerAwareTrait;
    use ViewAwareTrait;

    /**
     * A TransportFactory instance.
     *
     * @var \Viserio\Component\Mail\TransportFactory
     */
    private $transportFactory;

    /** @var \Viserio\Contract\Queue\QueueConnector */
    private $queueManager;

    /**
     * Create a new mail manager instance.
     *
     * @param array|ArrayAccess                        $config
     * @param \Viserio\Component\Mail\TransportFactory $transportFactory
     */
    public function __construct($config, TransportFactory $transportFactory)
    {
        parent::__construct($config);

        $this->transportFactory = $transportFactory;
    }

    /**
     * Set the queue manager.
     *
     * @param \Viserio\Contract\Queue\QueueConnector $queueManager
     *
     * @return void
     */
    public function setQueueManager(QueueContract $queueManager): void
    {
        $this->queueManager = $queueManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultConfig(): iterable
    {
        return [
            'default' => 'array',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Contract\Manager\Exception\InvalidArgumentException
     *
     * @return \Viserio\Contract\Mail\Mailer|\Viserio\Contract\Mail\QueueMailer
     */
    protected function create(array $config, string $method, string $errorMessage): MailerContract
    {
        if (isset($this->extensions[$config['name']])) {
            return $this->callCustomCreator($config['name'], $config);
        }

        if ($this->transportFactory->hasTransport($config['driver'])) {
            return $this->createMailer(
                $this->transportFactory->getTransport(
                    $config['driver'],
                    $config['transporter'] ?? []
                ),
                $config
            );
        }

        throw new InvalidArgumentException(\sprintf('Mailer [%s] is not supported.', $config['name']));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigFromName(string $name): array
    {
        $config = parent::getConfigFromName($name);

        $config['driver'] = $config['driver'] ?? $config['name'];

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    protected static function getConfigName(): string
    {
        return 'mail';
    }

    /**
     * Create a new SwiftMailer instance.
     *
     * @param Swift_Transport $transport
     *
     * @return Swift_Mailer
     */
    protected function createSwiftMailer(Swift_Transport $transport): Swift_Mailer
    {
        if (isset($this->resolvedOptions['domain'])) {
            Swift_DependencyContainer::getInstance()
                ->register('mime.idgenerator.idright')
                ->asValue($this->resolvedOptions['domain']);
        }

        return new Swift_Mailer($transport);
    }

    /**
     * Create a mailer or queue mailer instance.
     *
     * @param Swift_Transport $transport
     * @param array           $config
     *
     * @return \Viserio\Contract\Mail\Mailer|\Viserio\Contract\Mail\QueueMailer
     */
    private function createMailer(Swift_Transport $transport, array $config): MailerContract
    {
        $swiftMailer = $this->createSwiftMailer($transport);

        if ($this->queueManager !== null) {
            $mailer = new QueueMailer($swiftMailer, $this->queueManager, $config);
        } else {
            $mailer = new Mailer($swiftMailer, $config);
        }

        if ($this->container !== null) {
            $mailer->setContainer($this->container);
        }

        if ($this->viewFactory !== null) {
            $mailer->setViewFactory($this->viewFactory);
        }

        if ($this->eventManager !== null) {
            $mailer->setEventManager($this->eventManager);
        }

        // Next we will set all of the global addresses on this mailer, which allows
        // for easy unification of all "from" addresses as well as easy debugging
        // of sent messages since they get be sent into a single email address.
        foreach (['from', 'reply_to', 'to'] as $type) {
            $this->setGlobalAddress($mailer, $type);
        }

        return $mailer;
    }

    /**
     * Set a global address on the mailer by type.
     *
     * @param \Viserio\Contract\Mail\Mailer $mailer
     * @param string                        $type
     *
     * @return void
     */
    private function setGlobalAddress(MailerContract $mailer, string $type): void
    {
        if (! isset($this->resolvedOptions[$type])) {
            return;
        }

        $address = $this->resolvedOptions[$type];

        if (\is_array($address) && isset($address['address'])) {
            $mailer->{'always' . Str::studly($type)}($address['address'], $address['name']);
        }
    }
}
