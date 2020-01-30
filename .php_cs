<?php
use Narrowspark\CS\Config\Config;

$header = <<<'EOF'
This file is part of Narrowspark Framework.

(c) Daniel Bannert <d.bannert@anolilab.de>

This source file is subject to the MIT license that is bundled
with this source code in the file LICENSE.
EOF;

$config = new Config($header, [
    'native_function_invocation' => [
        'exclude' => [
            'fread',
            'fsockopen',
            'fclose',
            'pcntl_fork',
            'posix_setsid',
            'class_exists',
            'trigger_error',
            'file_exists',
            'dirname',
            'glob',
        ],
    ],
    'global_namespace_import' => [
        'import_classes' => true,
        'import_constants' => false,
        'import_functions' => false,
    ],
    'static_lambda' => false,
    'final_class' => false,
    'heredoc_indentation' => false,
    'PhpCsFixerCustomFixers/no_commented_out_code' => false,
    'PhpCsFixerCustomFixers/phpdoc_no_superfluous_param' => false,
    'phpdoc_to_return_type' => false,
]);

$config->getFinder()
    ->files()
    ->in(__DIR__)
    ->exclude('build/')
    ->exclude('vendor/')

    ->exclude('src/Viserio/Bridge/Monolog/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Bridge/Phpstan/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Bridge/Twig/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Bus/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Cache/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Console/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Container/Tests/Fixture/Compiled/')
    ->exclude('src/Viserio/Component/Cookie/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Cron/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Events/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Exception/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Filesystem/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Finder/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Foundation/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Http/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/HttpFactory/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/HttpFoundation/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Log/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Mail/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Manager/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Config/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Parser/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Path/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Profiler/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Routing/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Session/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Translation/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/Validation/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/View/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Component/WebServer/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Provider/Debug/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Provider/Framework/Tests/Container/Provider/Compiled/')
    ->exclude('src/Viserio/Provider/Twig/Tests/Container/Provider/Compiled/')

    ->notPath('src/Viserio/Component/Console/Test/CommandTestCase.php')

    ->notPath('src/Viserio/Component/Container/Test/AbstractContainerTestCase.php')
    ->notPath('src/Viserio/Component/Container/Tests/UnitTest/PhpParser/NodeVisitor/ClosureLocatorVisitorTest.php')
    ->notPath('src/Viserio/Component/Container/Tests/Fixture/TestFunctions.php')
    ->notPath('src/Viserio/Component/Container/Tests/Fixture/Proxy/proxy-factory.php')
    ->notPath('src/Viserio/Component/Container/Tests/Fixture/Proxy/proxy-implem.php')
    ->notPath('src/Viserio/Component/Container/Tests/Fixture/return_foo_string.php')
    ->notPath('src/Viserio/Component/Container/Tests/Fixture/Reflection/ExpandClassNoNamespace.php')
    ->notPath('src/Viserio/Component/Container/Tests/Fixture/Reflection/ExpandClassInBracketedNamespace.php')
    ->notPath('src/Viserio/Component/Container/Tests/Fixture/Reflection/ExpandClassInNamespace.php')

    ->notPath('src/Viserio/Component/Validation/Sanitizer.php')

    ->notPath('src/Viserio/Component/Http/HeaderSecurity.php')

    ->notPath('src/Viserio/Component/Support/helper.php')

    ->notPath('src/Viserio/Component/Profiler/Tests/Fixture/View/profilewithcollector.html.php')
    ->notPath('src/Viserio/Component/Profiler/Resource/views/profiler.html.php')

    ->notPath('phpunit.php')
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$cacheDir = getenv('TRAVIS') ? getenv('HOME') . '/.php-cs-fixer' : __DIR__;

$config->setCacheFile($cacheDir . '/.php_cs.cache');

return $config;
