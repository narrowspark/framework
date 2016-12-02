<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

use Symfony\Component\HttpKernel\DataCollectors\Util\ValueExporter;
use Viserio\Contracts\View\View as ViewContract;

class NarrowsparkCollector extends DataCollector
{
    /**
     * Normalized Version.
     *
     * @var string
     */
    protected $version;

    public function __construct(string $version)
    {
        $this->version = $version;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'narrowspark';
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgets()
    {
    }

    public function collect()
    {
        return [
            'narrowspark' => 'test',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAssets()
    {
        return [
            'css' => __DIR__ . '/../Resources/css/widgets/framework/widget.css',
            'js' => __DIR__ . '/../Resources/js/widgets/framework/widget.js',
        ];
    }
}
