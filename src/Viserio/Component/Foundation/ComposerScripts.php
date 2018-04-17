<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation;

use Composer\Script\Event;

class ComposerScripts
{
    /**
     * Handle the post-create-project Composer event.
     *
     * @param \Composer\Script\Event $event
     *
     * @return void
     */
    public static function onPostCreateProject(Event $event): void
    {
        $extra = self::getComposerExtraContent();
        $type  = self::getDiscoveryProjectType();

        if ($extra === null || $type === null) {
            return;
        }
    }

    /**
     * Get the composer extra values.
     *
     * @return array|null
     */
    private static function getComposerExtraContent(): ?array
    {
        $filePath = \getcwd() . '/composer.json';

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
     * @return string|null
     */
    private static function getDiscoveryProjectType(): ?string
    {
        $filePath = \getcwd() . '/discovery.lock';

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
