<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Console\Command;

use Symfony\Component\Console\Exception\RuntimeException;
use Viserio\Component\Console\Command\Command;
use Viserio\Component\Console\Traits\ConfirmableTrait;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;
use ParagonIE\Halite\Key;
use ParagonIE\Halite\KeyFactory;
use Viserio\Component\Session\SessionManager;

class KeyGenerateCommand extends Command
{
    use ConfirmableTrait;

    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'key:generate';

    /**
     * {@inheritdoc}
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
        $currentSessionKey    = $config->get('viserio.session.key_path', '');

        if ($currentEncryptionKey !== '' && $currentPasswordKey !== '') {
            $message = 'Your sure to overwrite your ';

            if (\class_exists(SessionManager::class)) {
                $message .= 'encryption session, ';
            }

            $message .= 'encryption and password key?';

            if (! $this->confirmToProceed($message)) {
                return 0;
            }
        }

        $this->removeKeysAndFolder(
            $keyFolderPath,
            $this->getEncryptionKeys($currentEncryptionKey, $currentPasswordKey, $currentSessionKey)
        );

        if (! \mkdir($keyFolderPath) && ! \is_dir($keyFolderPath)) {
            throw new RuntimeException(\sprintf('Directory "%s" was not created.', $keyFolderPath));
        }

        $this->saveKeyToFileAndPathToEnv(
            $keyFolderPath,
            $this->getEncryptionKeys($currentEncryptionKey, $currentPasswordKey, $currentSessionKey)
        );

        $this->info('Keys generated and set successfully.');

        return 0;
    }

    /**
     * Generate a random key for the application.
     *
     * @return \ParagonIE\Halite\Key
     */
    protected function generateRandomKey(): Key
    {
        return KeyFactory::generateEncryptionKey();
    }

    /**
     * Saves the key to a file and the file path to env vars.
     *
     * @param string $keyFolderPath
     * @param array  $keys
     *
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     *
     * @return void
     */
    protected function saveKeyToFileAndPathToEnv(string $keyFolderPath, array $keys): void
    {
        $encryptionKeyPath = $keyFolderPath . '/encryption_key';
        $passwordKeyPath   = $keyFolderPath . '/password_key';

        if (! KeyFactory::saveKeyToFile($encryptionKeyPath, $this->generateRandomKey())) {
            throw new RuntimeException('Encryption Key can\'t be created.');
        }

        if (! KeyFactory::saveKeyToFile($passwordKeyPath, $this->generateRandomKey())) {
            throw new RuntimeException('Password Key can\'t be created.');
        }

        if (\class_exists(SessionManager::class)) {
            $sessionKeyPath = $keyFolderPath . '/session_key';

            if (! KeyFactory::saveKeyToFile($sessionKeyPath, $this->generateRandomKey())) {
                throw new RuntimeException('Session Key can\'t be created.');
            }

            $this->replaceEnvVariableValue('ENCRYPTION_SESSION_KEY_PATH', $keys['session'], $sessionKeyPath);
        }

        $this->replaceEnvVariableValue('ENCRYPTION_KEY_PATH', $keys['encryption'], $encryptionKeyPath);
        $this->replaceEnvVariableValue('ENCRYPTION_PASSWORD_KEY_PATH', $keys['password'], $passwordKeyPath);
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
     * @param string $keyFolderPath
     * @param array  $keys
     *
     * @return void
     */
    private function removeKeysAndFolder(string $keyFolderPath, array $keys): void
    {
        foreach ($keys as $key) {
            if ($key !== '' && \is_file($key)) {
                \unlink($key);
            }
        }

        if (\is_dir($keyFolderPath)) {
            \rmdir($keyFolderPath);
        }
    }

    /**
     * Get the default keys.
     *
     * @param null|string $currentEncryptionKey
     * @param null|string $currentPasswordKey
     * @param null|string $currentSessionKey
     *
     * @return array
     */
    private function getEncryptionKeys(
        ?string $currentEncryptionKey,
        ?string $currentPasswordKey,
        ?string $currentSessionKey
    ): array {
        return [
            'encryption' => $currentEncryptionKey,
            'password'   => $currentPasswordKey,
            'session'    => $currentSessionKey,
        ];
    }
}
