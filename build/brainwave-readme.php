<?php

$sourceDirectory = __DIR__.'/../src/Brainwave';
$readmeTemplate  = __DIR__.'/brainwave-readme.md';

$dirs = glob($sourceDirectory.'/*', GLOB_ONLYDIR);

foreach ($dirs as $dir) {
    // extract part of the folder name
    $parts = explode('/', $dir);
    // set variables
    list($vendor, $name) = array_slice($parts, - 2);
    $package = strtolower($vendor.'/'.$name);
    $readme  = $sourceDirectory.'/'.$name.'/README.md';
    // get template
    $content = file_get_contents($readmeTemplate);
    // replace variables in template
    $replacements = [
        '@name'    => $name,
        '@vendor'  => $vendor,
        '@package' => $package,
    ];

    echo "Created README.md in {$package}</br>\r\n";

    $output       = str_replace(array_keys($replacements), array_values($replacements), $content);
    // write package readme
    file_put_contents($readme, $output);
}
