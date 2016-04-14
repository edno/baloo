<?php

namespace Baloo;

/**
 * class BalooContext
 * Class that manages global BALOO execution context (mandatory).
 */

use Baloo\BalooException;

class BalooContext
{
    use Singleton;

    public static $debug = false;

    private static $folders = array(
        'lib' => '../lib',
        'core' => '../core',
    );

    public static $tablePrefix = '_';
    public static $rootDir = __DIR__;

    public static $logger = null;

    private static $pdo = null;

    protected function __init()
    {
        self::$logger = new BalooLogger();
    }

    /**
     * Method for loading libs dynamically.
     *
     * @static
     *
     * @param string $lib    Name (without suffix) of the library to be loaded,
     *      if null load all libraries found in specified folder (default=null)
     * @param string $folder Folder that contains libraries (default='lib')
     *
     * @return bool
     */
    public static function loadLibrary($lib = null, $folder = 'lib')
    {
        try {
            $path = self::folder($folder);
            if (is_null($lib) === true) {
                $dir = dir($path);
                while (false !== ($entry = $d->read())) {
                    include_once $path.'/'.$entry;
                }
                $d->close();
                unset($d);
            } else {
                include_once $path.'/'.$lib.'.php';
            }

            return true;
        } catch (\Exception $e) {
            throw new BalooException('ERROR: Failed to open library '.$lib.' in  folder '.$folder.'!');
        }
    }

    /**
     * Get folder full path.
     *
     * @static
     *
     * @param string $key Folder's name to retreive path
     *
     * @return string Folder path
     */
    public static function folder($key)
    {
        return self::$rootDir.'/'.self::$folders[$key];
    }

    /**
     * Get folders list.
     *
     * @static
     *
     * @return array Folders list
     */
    public static function getFolders()
    {
        return array_map(
            function ($folder) {
                return self::$rootDir.'/'.$folder;
            },
            self::$folders
        );
    }

    public function setPDO(\PDO $pdo)
    {
        self::$pdo = $pdo;
    }

    public function getPDO()
    {
        return self::$pdo;
    }
}
