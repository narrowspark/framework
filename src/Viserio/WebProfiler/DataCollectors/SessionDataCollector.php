<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\WebProfiler\TabAware as TabAwareContract;
use Viserio\Contracts\Session\Store as StoreContract;

class SessionDataCollector extends AbstractDataCollector implements TabAwareContract
{
    /**
     * A server request instance.
     *
     * @var array
     */
    protected $sessions;

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest)
    {
        $sessions = [];

            var_dump($serverRequest);
        foreach ($serverRequest->getAttributes() as $name => $value) {
            if ($value instanceof StoreContract) {
                $sessions[] = $value;
            }
        }

        $this->sessions = $sessions;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'sessions';
    }

    /**
     * {@inheritdoc}
     */
    public function getTabPosition(): string
    {
        return 'left';
    }

    /**
     * {@inheritdoc}
     */
    public function getTab(): array
    {
        return [
            'icon' => '',
            'label' => 'Sessions',
            'count' => count($this->sessions),
        ];
    }
}
