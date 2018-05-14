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
        ],
    ],
    'static_lambda' => false,
    'final_class' => false,
    'heredoc_indentation' => false,
    'PhpCsFixerCustomFixers/no_commented_out_code' => false,
    'PhpCsFixerCustomFixers/phpdoc_no_superfluous_param' => false,
    'PhpCsFixerCustomFixers/data_provider_return_type' => true,
    'PhpCsFixerCustomFixers/data_provider_name' => true,
    'PhpCsFixerCustomFixers/comment_surrounded_by_spaces' => true,
    'PhpCsFixerCustomFixers/no_duplicated_imports' => true,
    'PhpCsFixerCustomFixers/no_useless_sprintf' => true,
    'PhpCsFixerCustomFixers/php_unit_no_useless_return' => true,
    'PhpCsFixerCustomFixers/single_line_throw' => true,
    'php_unit_test_case_static_method_calls' => [
        'call_type' => 'self',
    ],
    'phpdoc_to_return_type' => false,
]);

$config->getFinder()
    ->files()
    ->in(__DIR__)
    ->exclude('build/')
    ->exclude('vendor/')
    ->exclude('src/Viserio/Component/Container/Tests/Fixture/Compiled/')
    ->notPath('src/Viserio/Component/HttpFoundation/Tests/Container/Provider/Compiled/WebServerServiceProviderContainer.php')
    ->notPath('src/Viserio/Component/Console/Tester/CommandTestCase.php')
    ->notPath('src/Viserio/Component/Container/Tester/AbstractContainerTestCase.php')
    ->notPath('src/Viserio/Component/Container/Tests/UnitTest/PhpParser/NodeVisitor/ClosureLocatorVisitorTest.php')
    ->notPath('src/Viserio/Component/Profiler/Resource/views/profiler.html.php')
    ->notPath('src/Viserio/Component/Container/Tests/Fixture/Proxy/proxy-factory.php')
    ->notPath('src/Viserio/Component/Container/Tests/Fixture/Proxy/proxy-implem.php')
    ->notPath('src/Viserio/Component/OptionsResolver/Traits/OptionsResolverTrait.php')
    ->notPath('src/Viserio/Component/Validation/Sanitizer.php')
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$cacheDir = getenv('TRAVIS') ? getenv('HOME') . '/.php-cs-fixer' : __DIR__;

$config->setCacheFile($cacheDir . '/.php_cs.cache');

return $config;
