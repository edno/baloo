<?php

define('ISCLI', PHP_SAPI === 'cli');

require_once __DIR__.'/../vendor/autoload.php';

use Baloo\Packman\Packman;
use Baloo\BalooContext;
use Baloo\BalooPDO;

if (ISCLI === true) {
    try {
        new BalooContext(new BalooPDO(null, null, null, null, 'memory'));

        echo PHP_EOL.PHP_EOL;
        echo '-------'.Packman::NAME.'-------'.PHP_EOL;
        echo PHP_EOL.PHP_EOL;
        echo '> Package to install? ';
        $file = Packman::readConsole();

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
                $response = Packman::readConsole();
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
