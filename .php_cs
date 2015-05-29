<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->exclude('build')
    ->exclude('vendor')
    ->in(__DIR__)
    ->notName('*.phar')
    ->notName('LICENSE')
    ->notName('README.md')
    ->notName('composer.*')
    ->notName('phpunit.xml.dist')
    ->notName('.scrutinizer.yml')
    ->notName('.travis.yml')
    ->notName('phpcs.xml')
    ->notName('coveralls.yml')
    ->notName('.styleci.yml')
    ->notName('CONTRIBUTING')
    ->notName('.php_cs')
    ->notName('IntervalTrait.php');

if (file_exists(__DIR__.'/local.php_cs')) {
    require __DIR__.'/local.php_cs';
}

return Symfony\CS\Config\Config::create()
    // use default PSR-2_LEVEL:
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers(
        [
            'ordered_use',
            'short_array_syntax',
            'strict',
            'strict_param',
            '-no_empty_lines_after_phpdocs',
            'header_comment',
            'newline_after_open_tag',
            'phpdoc_order',
        ]
    )
    ->finder($finder);
