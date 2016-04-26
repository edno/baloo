<?php
require_once __DIR__.'/../vendor/autoload.php';

use Baloo\Packman\Packman;
use Baloo\Packman\PackmanException;
use Baloo\BalooContext;

BalooContext::loadLibrary('console'); // workaround for non-class namespace
use Baloo\Lib\Console;

if (Console\ISCLI === true) {
    try {
        new BalooContext(new \PDO('sqlite:/tmp/baloo.db'));

        echo PHP_EOL.PHP_EOL;
        echo '-------'.Packman::NAME.'-------'.PHP_EOL;
        echo PHP_EOL.PHP_EOL;
        echo '> Package to install? ';
        $file = Console\read_console();

        echo 'INFO: Loading "'.$file.'"...'.PHP_EOL;
        $pack = Packman::loadPackFile($file);

        echo 'INFO: Installing "'.$file.'"...'.PHP_EOL;
        try {
            $status = Packman::installPack($pack);
        } catch (Exception $e) {
            $status = false;
            if ($e->getCode() == 100 || $e->getCode() == 101) {
                echo 'INFO: '.$e->getMessage().PHP_EOL.PHP_EOL;
                echo '> Overwrite current "'.$file.'" package (y|n)? ';
                $response = Console\read_console();
                if (preg_match('/y/i', $response)) {
                    Packman::removePack($pack);
                    $status = Packman::installPack($pack);
                }
            } else {
                throw $e;
            }
        }

        if ($status === true) {
            echo 'INFO: Installation of package "'.$file.'" finished.'.PHP_EOL;
        } else {
            throw new PackManException('Installation of package "'.$file.'" failed!!!');
        }
    } catch (Exception $e) {
        echo 'ERROR: '.$e->getMessage().PHP_EOL;
        exit($e->getCode());
    }
} else {
    echo '<h1>'.NAME.' is only available via CLI</h1>'.PHP_EOL;
}

echo PHP_EOL;
