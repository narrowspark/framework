<?php
declare(strict_types=1);
namespace Viserio\Encryption\Commands;

use Viserio\Console\Command\Command;
use Viserio\Console\Traits\ConfirmableTrait;
use Viserio\Contracts\Config\Repository as RepositoryContract;

class KeyGenerateCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'key:generate
        {--show : Display the key instead of modifying files}
        {--force : Force the operation to run when in production}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Set the encryption key';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $key = $this->generateRandomKey();

        if ($this->option('show')) {
            return $this->line('<comment>'.$key.'</comment>');
        }

        // Next, we will replace the application key in the environment file so it is
        // automatically setup for this developer. This key gets generated using a
        // secure random byte generator and is later base64 encoded for storage.
        if (! $this->setKeyInEnvironmentFile($key)) {
            return;
        }

        $this->getContainer()->get(RepositoryContract::class)->set('app.key', $key);

        $this->info("Application key [$key] set successfully.");
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
        $config = $this->getContainer()->get(RepositoryContract::class);
        $currentKey = $config->get('app.key');

        if (strlen($currentKey) !== 0 && (! $this->confirmToProceed())) {
            return false;
        }

        $env = $this->config->get('path.env');

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
