<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation;

use Composer\Script\Event;
use Viserio\Component\Foundation\Project\GenerateFolderStructureAndFiles;

/**
 * @internal
 */
final class ComposerScripts
{
    /**
     * Handle the post-create-project Composer event.
     *
     * @param \Composer\Script\Event $event
     * @param null|string            $testPath
     *
     * @return void
     */
    public static function onPostCreateProject(Event $event, string $testPath = null): void
    {
        $workDir = $testPath ?? \getcwd();

        $extra = self::getComposerExtraContent($workDir);
        $type  = self::getDiscoveryProjectType($workDir);

        if ($extra === null && $type === null) {
            return;
        }

        GenerateFolderStructureAndFiles::create($extra, $type, $event->getIO());
    }

    /**
     * Get the composer extra values.
     *
     * @param string $workDir
     *
     * @return null|array
     */
    private static function getComposerExtraContent(string $workDir): ?array
    {
        $filePath = $workDir . '/composer.json';

        if (! \file_exists($filePath)) {
            return null;
        }

        $data = \json_decode(\file_get_contents($filePath), true);

        if (! isset($data['extra'])) {
            return null;
        }

        return $data['extra'];
    }

    /**
     * Get the discovery project type.
     *
     * @param string $workDir
     *
     * @return null|string
     */
    private static function getDiscoveryProjectType(string $workDir): ?string
    {
        $filePath = $workDir . '/discovery.lock';

        if (! \file_exists($filePath)) {
            return null;
        }

        $data = \json_decode(\file_get_contents($filePath), true);

        if (! isset($data['project-type'])) {
            return null;
        }

        return $data['project-type'];
    }
}
