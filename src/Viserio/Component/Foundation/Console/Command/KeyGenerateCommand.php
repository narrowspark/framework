<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Console\Command;

use ParagonIE\ConstantTime\Hex;
use Viserio\Component\Console\Command\Command;
use Viserio\Component\Console\Traits\ConfirmableTrait;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;
use Viserio\Component\Contract\Encryption\Security as SecurityContract;
use Viserio\Component\Encryption\Key;
use Viserio\Component\Encryption\KeyFactory;

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
        $key        = $this->generateRandomKey();
        $encodedKey = $this->encodeKey($key);
        $container  = $this->getContainer();

        if ($this->option('show') || ! $container->has(RepositoryContract::class)) {
            $this->line('<comment>' . $encodedKey . '</comment>');

            return 0;
        }

        // Next, we will replace the application key in the environment file so it is
        // automatically setup for this developer. This key gets generated using sodium.
        if (! $this->setKeyInEnvironmentFile($encodedKey)) {
            \sodium_memzero($encodedKey);

            return 1;
        }

        $container->get(RepositoryContract::class)->set('viserio.app.key', $key);

        $this->info(sprintf('Application key [%s] set successfully.', $encodedKey));

        \sodium_memzero($encodedKey);

        return 0;
    }

    /**
     * Set the application key in the environment file.
     *
     * @param string $encodedKey
     *
     * @return bool
     */
    protected function setKeyInEnvironmentFile(string $encodedKey): bool
    {
        $container  = $this->getContainer();
        $currentKey = $container->get(RepositoryContract::class)->get('viserio.app.key');

        if ($currentKey !== null && (! $this->confirmToProceed())) {
            return false;
        }

        $env        = $container->get(ConsoleKernelContract::class)->getEnvironmentFilePath();
        $currentKey = $currentKey instanceof Key ? $this->encodeKey($currentKey) :'';

        \file_put_contents($env, \str_replace(
            'APP_KEY=' . $currentKey,
            'APP_KEY=' . $encodedKey,
            \file_get_contents($env)
        ));

        return true;
    }

    /**
     * Generate a random key for the application.
     *
     * @return \Viserio\Component\Encryption\Key
     */
    protected function generateRandomKey(): Key
    {
        $secret = \random_bytes(32);

        return KeyFactory::generateKey($secret);
    }

    /**
     * @param \Viserio\Component\Encryption\Key $key
     *
     * @return string
     */
    private function encodeKey(Key $key): string
    {
        return Hex::encode(
            SecurityContract::SODIUM_PHP_VERSION . $key->getRawKeyMaterial() .
            \sodium_crypto_generichash(
                SecurityContract::SODIUM_PHP_KEY_VERSION . $key->getRawKeyMaterial(),
                '',
                \SODIUM_CRYPTO_GENERICHASH_BYTES_MAX
            )
        );
    }
}
