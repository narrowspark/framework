<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->files()
    ->in(__DIR__)
    ->exclude('build')
    ->exclude('vendor')
    ->notName('*.phar')
    ->notName('CONTRIBUTING')
    ->notName('IntervalTrait.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

if (file_exists(__DIR__.'/local.php_cs')) {
    require __DIR__.'/local.php_cs';
}

return Symfony\CS\Config\Config::create()
    // use default PSR-2_LEVEL:
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers(
        [
            '-psr0',
            'phpdoc_order',
            'ordered_use',
            'short_array_syntax',
            'strict',
            'strict_param',
        ]
    )
    ->finder($finder)
    ->setUsingCache(true);
