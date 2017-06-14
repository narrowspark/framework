<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Console\Command;

use Defuse\Crypto\Key;
use Viserio\Component\Console\Command\Command;
use Viserio\Component\Console\Traits\ConfirmableTrait;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Console\Kernel as ConsoleKernelContract;

class KeyGenerateCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'key:generate [--show= : Display the key instead of modifying files] [--force= : Force the operation to run when in production]';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Set the encryption key.';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $key       = $this->generateRandomKey();
        $container = $this->getContainer();

        if ($this->option('show') || ! $container->has(RepositoryContract::class)) {
            $this->line('<comment>' . $key . '</comment>');

            return 0;
        }

        // Next, we will replace the application key in the environment file so it is
        // automatically setup for this developer. This key gets generated using
        // https://github.com/defuse/php-encryption
        if (! $this->setKeyInEnvironmentFile($key)) {
            return 1;
        }

        $container->get(RepositoryContract::class)->set('viserio.app.key', $key);

        $this->info("Application key [$key] set successfully.");

        return 0;
    }

    /**
     * Set the application key in the environment file.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function setKeyInEnvironmentFile(string $key): bool
    {
        $container  = $this->getContainer();
        $currentKey = $container->get(RepositoryContract::class)->get('viserio.app.key', '');

        if (mb_strlen($currentKey) !== 0 && (! $this->confirmToProceed())) {
            return false;
        }

        $env = $container->get(ConsoleKernelContract::class)->getEnvironmentFilePath();

        file_put_contents($env, str_replace(
            'APP_KEY=' . $currentKey,
            'APP_KEY=' . $key,
            file_get_contents($env)
        ));

        return true;
    }

    /**
     * Generate a random key for the application.
     *
     * @return string
     */
    protected function generateRandomKey(): string
    {
        $key = Key::createNewRandomKey();

        return $key->saveToAsciiSafeString();
    }
}
