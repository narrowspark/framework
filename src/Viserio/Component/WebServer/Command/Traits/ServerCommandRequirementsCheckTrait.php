<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\WebServer\Command\Traits;

/**
 * @method null|array|string option($key = null)
 * @method void              error(string $string, $verbosityLevel = null)
 * @method bool              hasOption(string $key)
 */
trait ServerCommandRequirementsCheckTrait
{
    /**
     * The document (public folder) for the server.
     *
     * @var null|string
     */
    private $documentRoot;

    /**
     * The env for the server.
     *
     * @var null|string
     */
    private $environment;

    /**
     * Check the requirements for the command.
     *
     * @return int
     */
    protected function checkRequirements(): int
    {
        if ($this->documentRoot === null) {
            if ($this->hasOption('docroot')) {
                $this->documentRoot = $this->option('docroot');
            } else {
                $this->error('The document root directory must be either passed as first argument of the constructor or through the "--docroot" input option.');

                return 1;
            }
        }

        if ($this->environment === null) {
            if ($this->hasOption('env')) {
                if (\is_string($env = $this->option('env'))) {
                    $this->environment = $env;
                } else {
                    $this->error('The environment must be either passed as second argument of the constructor or through the "--env" input option.');

                    return 1;
                }
            } else {
                $this->error('The environment must be passed as second argument of the constructor.');

                return 1;
            }
        }

        if ($this->environment === 'prod') {
            $this->error('Running this server in production environment is NOT recommended!');
        }

        return 0;
    }
}
