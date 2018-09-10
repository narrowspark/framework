<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Composer;

use Composer\Script\Event;

class ComposerScripts
{
    /**
     * Handle the pre-install Composer event.
     *
     * @param  \Composer\Script\Event  $event
     *
     * @return void
     */
    public static function preInstall(Event $event): void
    {
    }

    /**
     * Handle the pre-update Composer event.
     *
     * @param  \Composer\Script\Event  $event
     * @return void
     */
    public static function preUpdate(Event $event): void
    {
    }

    /**
     * Handle the post-autoload-dump Composer event.
     *
     * @param  \Composer\Script\Event  $event
     * @return void
     */
    public static function postAutoloadDump(Event $event): void
    {
    }
}
