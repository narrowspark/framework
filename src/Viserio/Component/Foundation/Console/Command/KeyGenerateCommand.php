<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Console\Command;

use Symfony\Component\Console\Exception\RuntimeException;
use Viserio\Component\Console\Command\Command;
use Viserio\Component\Console\Traits\ConfirmableTrait;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;
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
    protected $signature = 'key:generate
        [--force= : Force the operation to run when in production]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Set the encryption key.';

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     */
    public function handle(RepositoryContract $config, ConsoleKernelContract $consoleKernel)
    {
        $keyFolderPath        = $consoleKernel->getStoragePath('keysring');
        $currentEncryptionKey = $config->get('viserio.encryption.key_path', '');
        $currentPasswordKey   = $config->get('viserio.encryption.password_key_path', '');

        if ($currentEncryptionKey !== '' &&
            $currentPasswordKey !== '' &&
            (! $this->confirmToProceed('Your sure to overwrite your encryption and password key?'))
        ) {
            return 0;
        }

        $this->removeKeysAndFolder($currentEncryptionKey, $currentPasswordKey, $keyFolderPath);

        if (! mkdir($keyFolderPath) && ! is_dir($keyFolderPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created.', $keyFolderPath));
        }

        $this->saveKeyToFileAndPathToEnv(
            $this->generateRandomKey(),
            $this->generateRandomKey(),
            $keyFolderPath,
            $currentEncryptionKey,
            $currentPasswordKey
        );

        $this->info('Application & Password key set successfully.');

        return 0;
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
     * Replace a env key with given value.
     *
     * @param string $key
     * @param string $currentKeyString
     * @param string $encodedKey
     *
     * @return void
     */
    private function replaceEnvVariableValue(
        string $key,
        string $currentKeyString,
        string $encodedKey
    ): void {
        $envPath = $this->getContainer()->get(ConsoleKernelContract::class)
            ->getEnvironmentFilePath();

        \file_put_contents($envPath, \str_replace(
            $key . '=' . $currentKeyString,
            $key . '=' . $encodedKey,
            \file_get_contents($envPath)
        ));
    }

    /**
     * Removes old keys if they exits.
     *
     * @param string $currentEncryptionKey
     * @param string $currentPasswordKey
     * @param string $keyFolderPath
     *
     * @return void
     */
    private function removeKeysAndFolder($currentEncryptionKey, $currentPasswordKey, $keyFolderPath): void
    {
        if ($currentEncryptionKey !== '' && $currentPasswordKey !== '') {
            \unlink($currentEncryptionKey);
            \unlink($currentPasswordKey);
        }

        if (\is_dir($keyFolderPath)) {
            \rmdir($keyFolderPath);
        }
    }

    /**
     * Saves the key to a file and the file path to env vars.
     *
     * @param \Viserio\Component\Encryption\Key $encryptionKey
     * @param \Viserio\Component\Encryption\Key $passwordKey
     * @param string                            $keyFolderPath
     * @param string                            $currentEncryptionKey
     * @param string                            $currentPasswordKey
     *
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     *
     * @return void
     */
    protected function saveKeyToFileAndPathToEnv(
        Key $encryptionKey,
        Key $passwordKey,
        string $keyFolderPath,
        string $currentEncryptionKey,
        string $currentPasswordKey
    ): void {
        $encryptionKeyPath = $keyFolderPath . '/encryption_key';
        $passwordKeyPath = $keyFolderPath . '/password_key';

        if (! KeyFactory::saveKeyToFile($encryptionKeyPath, $encryptionKey)) {
            throw new RuntimeException('Encryption Key can\'t be created.');
        }

        if (! KeyFactory::saveKeyToFile($passwordKeyPath, $passwordKey)) {
            throw new RuntimeException('Password Key can\'t be created.');
        }

        $this->replaceEnvVariableValue('ENCRYPTION_KEY_PATH', $currentEncryptionKey, $encryptionKeyPath);
        $this->replaceEnvVariableValue('ENCRYPTION_PASSWORD_KEY_PATH', $currentPasswordKey, $passwordKeyPath);
    }
}
