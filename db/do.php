<?php

error_reporting(0);
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/../functions.php';
error_reporting(E_ALL);

if (count($argv) === 1) {
    echo <<<HELP

        Usage: $argv[0] <type> [mask]
        
        - type - migration/seed
        - mask - file name mask (glob)


    HELP;
    die;
}

$type = $argv[1];
$tpl = $argv[2] ?? '*';
$files = glob(__DIR__ . "/${type}s/$tpl");

if (empty($files)) {
    echo "\nNo files\n\n";
    die;
}

sort($files);
$migrations = array_map(function($file) {
    global $CONNECTION;

    echo "$file\n";

    $migration = '';

    if (preg_match('!\\.php$!', $file)) {
        ob_start();
        include $file;
        $migration = ob_get_clean();
    } else {
        $migration = file_get_contents($file);
    }

    $migration = trim($migration);

    if ($migration) {
        $CONNECTION->multi_query($migration);

        do {
            echo '.';
            $CONNECTION->store_result();

            if ($CONNECTION->error) {
                echo "\n", $CONNECTION->error, "\n";
            }
        } while ($CONNECTION->next_result());

        echo "\n";
    }
}, $files);

