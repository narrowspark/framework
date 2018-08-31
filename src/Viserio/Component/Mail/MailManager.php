<?php
declare(strict_types=1);
namespace Viserio\Component\Mail;

use Swift_DependencyContainer;
use Swift_Mailer;
use Swift_Transport;
use Viserio\Component\Contract\Events\Traits\EventManagerAwareTrait;
use Viserio\Component\Contract\Mail\Mailer as MailerContract;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\Queue\QueueConnector as QueueContract;
use Viserio\Component\Contract\Support\Exception\InvalidArgumentException;
use Viserio\Component\Contract\View\Traits\ViewAwareTrait;
use Viserio\Component\Support\AbstractConnectionManager;
use Viserio\Component\Support\Str;

class MailManager extends AbstractConnectionManager implements ProvidesDefaultOptionsContract
{
    use EventManagerAwareTrait;
    use ViewAwareTrait;

    /**
     * A TransportFactory instance.
     *
     * @var \Viserio\Component\Mail\TransportFactory
     */
    private $transportFactory;

    /**
     * @var \Viserio\Component\Contract\Queue\QueueConnector
     */
    private $queueManager;

    /**
     * Create a new mail manager instance.
     *
     * @param array|\ArrayAccess                       $config
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
     * @param \Viserio\Component\Contract\Queue\QueueConnector $queueManager
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
    public static function getDefaultOptions(): array
    {
        return [
            'default' => 'array',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Component\Contract\Support\Exception\InvalidArgumentException
     *
     * @return \Viserio\Component\Contract\Mail\Mailer|\Viserio\Component\Contract\Mail\QueueMailer
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
     * @param \Swift_Transport $transport
     *
     * @return \Swift_Mailer
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
     * @param \Swift_Transport $transport
     * @param array            $config
     *
     * @return \Viserio\Component\Contract\Mail\Mailer|\Viserio\Component\Contract\Mail\QueueMailer
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
     * @param \Viserio\Component\Contract\Mail\Mailer $mailer
     * @param string                                  $type
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
