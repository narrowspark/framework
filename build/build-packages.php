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

if (3 > $_SERVER['argc']) {
    echo "Usage: branch dir1 dir2 ... dirN\n";

    exit(1);
}

\chdir(\dirname(__DIR__));

$json = \ltrim(\file_get_contents('composer.json'));

if ($json !== $package = \preg_replace('/\n    "repositories": \[\n.*?\n    \],/s', '', $json)) {
    \file_put_contents('composer.json', $package);
}

$dirs = $_SERVER['argv'];

\array_shift($dirs);

$mergeBase = \trim(\shell_exec(\sprintf('git merge-base "%s" HEAD', \array_shift($dirs))));

$packages = [];
$flags = \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE;

$composerConfigFile = @\file_get_contents(__DIR__ . '/composer-config.json');
$preferredInstall = [];

if ($composerConfigFile !== false) {
    $composerConfig = \json_decode($composerConfigFile, true);
    $preferredInstall = $composerConfig['config']['preferred-install'];
}

foreach ($dirs as $k => $dir) {
    if (! \system("git diff --name-only {$mergeBase} -- {$dir}", $exitStatus)) {
        if ($exitStatus) {
            exit($exitStatus);
        }

        unset($dirs[$k]);

        continue;
    }

    echo "{$dir}\n";

    $json = \ltrim(\file_get_contents($dir . '/composer.json'));

    if (null === $package = \json_decode($json)) {
        \passthru("composer validate {$dir}/composer.json");

        exit(1);
    }

    $package->repositories = [[
        'type' => 'composer',
        'url' => 'file://' . \str_replace(\DIRECTORY_SEPARATOR, '/', \dirname(__DIR__)) . '/',
    ]];

    if (\strpos($json, "\n    \"repositories\": [\n") === false) {
        $json = \rtrim(\json_encode(['repositories' => $package->repositories], $flags), "\n}") . ',' . \substr($json, 1);
        \file_put_contents($dir . '/composer.json', $json);
    }

    if (isset($preferredInstall[$package->name]) && ($preferredInstall[$package->name] === 'source')) {
        \passthru("cd {$dir} && tar -cf package.tar --exclude='package.tar' *");
    } else {
        \passthru("cd {$dir} && git init && git add . && git commit -m - && git archive -o package.tar HEAD && rm .git/ -Rf");
    }

    if (! isset($package->extra->{'branch-alias'}->{'dev-master'})) {
        echo "Missing \"dev-master\" branch-alias in composer.json extra.\n";

        exit(1);
    }

    $package->version = \str_replace('-dev', '.x-dev', $package->extra->{'branch-alias'}->{'dev-master'});
    $package->dist['type'] = 'tar';
    $package->dist['url'] = 'file://' . \str_replace(\DIRECTORY_SEPARATOR, '/', \dirname(__DIR__)) . "/{$dir}/package.tar";

    $packages[$package->name][$package->version] = $package;

    $versions = @\file_get_contents('https://repo.packagist.org/p/' . $package->name . '.json') ?: \sprintf('{"packages":{"%s":{"dev-master":%s}}}', $package->name, \file_get_contents($dir . '/composer.json'));
    $versions = \json_decode($versions)->packages->{$package->name};

    if (isset($versions->{'dev-master'}) && $package->version === \str_replace('-dev', '.x-dev', $versions->{'dev-master'}->extra->{'branch-alias'}->{'dev-master'})) {
        unset($versions->{'dev-master'});
    }

    foreach ($versions as $v => $package) {
        $packages[$package->name] += [$v => $package];
    }
}

\file_put_contents('packages.json', \json_encode(\compact('packages'), $flags));

if (count($dirs) !== 0) {
    $json = \ltrim(\file_get_contents('composer.json'));

    if (null === $package = \json_decode($json)) {
        \passthru("composer validate {$dir}/composer.json");

        exit(1);
    }

    $package->repositories = [[
        'type' => 'composer',
        'url' => 'file://' . \str_replace(\DIRECTORY_SEPARATOR, '/', \dirname(__DIR__)) . '/',
    ]];

    $json = \rtrim(\json_encode(['repositories' => $package->repositories], $flags), "\n}") . ',' . \substr($json, 1);

    \file_put_contents('composer.json', $json);
}
