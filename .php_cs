<?php
use Narrowspark\CS\Config\Config;

$config = new Config(null, [
    'native_function_invocation' => [
        'exclude' => [
            'fread',
            'fsockopen',
            'fclose',
            'pcntl_fork',
            'posix_setsid',
            'class_exists',
            'trigger_error'
        ],
    ],
]);

$config->getFinder()
    ->files()
    ->in(__DIR__)
    ->exclude('build')
    ->exclude('vendor')
    ->notPath('src/Viserio/Component/Validation/Sanitizer.php')
    ->notPath('src/Viserio/Component/Console/Tester/CommandTestCase.php')
    ->notPath('src/Viserio/Component/Profiler/Resource/views/profiler.html.php')
    ->notPath('src/Viserio/Component/Container/Tests/IntegrationTest/ContainerMakeTest.php')
    ->notPath('src/Viserio/Component/Container/Tests/Fixture/OptionalParameterFollowedByRequiredParameter.php')
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$cacheDir = getenv('TRAVIS') ? getenv('HOME') . '/.php-cs-fixer' : __DIR__;

$config->setCacheFile($cacheDir . '/.php_cs.cache');

return $config;
