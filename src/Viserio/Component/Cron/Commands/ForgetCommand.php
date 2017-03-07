<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Commands;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Input\InputArgument;
use Viserio\Component\Console\Command\Command;

class ForgetCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cron:forget';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove an item from the cache';

    /**
     * The psr-6 instance.
     *
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    protected $cache;

    /**
     * Create a new cache clear command instance.
     *
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     */
    public function __construct(CacheItemPoolInterface $cache)
    {
        parent::__construct();

        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $this->cache->deleteItem($this->argument('key'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getArguments(): array
    {
        return [
            ['key', InputArgument::REQUIRED, 'The name of the key.'],
        ];
    }
}
