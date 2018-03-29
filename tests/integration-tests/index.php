<?php

ini_set('display_errors', 1);

if (!$autoloader = @include __DIR__ . '/../../vendor/autoload.php') {
    http_response_code(500);
    echo "Composer autoloader is missing.\n";
    exit;
};

$tests = [];
$dir = new DirectoryIterator(__DIR__);
foreach ($dir as $file) {

    if ($file->getExtension() !== 'php') continue;
    if ($file->getFilename() === 'index.php') continue;

    $tests[] = substr($file->getFilename(), 0, -4);
}

array_walk($tests, function (&$file) {
    $file = sprintf('<a href="?test=%s">%s</a>', $file, $file);
});

array_unshift($tests, '<a href="/">Disable test</a>');
$list = implode($tests, "\n<br>");

echo <<<HTML
<h2>Tests</h2>
<ul>$list</ul>

HTML;

if (php_sapi_name() === 'cli') {
    $test = (count($argv) === 2) ? array_pop($argv) : '';
} else {
    $test = (isset($_GET['test']) && preg_match('/[a-z0-9]{1,100}/i', $_GET['test'])) ? $_GET['test'] : '';
}

if (!$test) exit;

$file = sprintf('%s/%s.php', __DIR__, $test);
if (!file_exists($file)) exit;

echo <<<HTML
<h2>Loading Test</h2>
<ul>$file</ul>

HTML;

include $file;
