<?php

define('ISCLI', PHP_SAPI === 'cli');

require_once 'core/BalooContext.class.php';

//@TODO make something cleaner => singleton or pur static instantiation
new BalooContext(__DIR__);

function __autoload($className) {
	$folders = BalooContext::getFolders();
	foreach($folders as $folder => $path) {
		$path = $path . $className . BalooContext::folderExt($folder);
		if(file_exists($path)) {
			require_once $path;
			return true;
		}
	}
	return false;
}

try {
	//BalooContext::$pdo = new BalooPDO('a162787_packman', 'localhost', 'a162787_packman', 'packman');
} catch(Exception $e) {
	echo 'ERROR: '. $e->getMessage() . PHP_EOL;
	exit($e->getCode());
}