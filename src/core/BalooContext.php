<?php

namespace Baloo;

/**
 * class BalooContext
 * Class that manages global BALOO execution context (mandatory).
 */
 
use Baloo\BalooException;
 
class BalooContext
{
    public static $debug = false;

    private static $folders = array(
        'lib' => '../lib',
        'core' => '../core',
    );

    public static $pdo = null;
    public static $tablePrefix = '_';
    public static $rootDir = __DIR__;

    public static $logger = null;

    public function __construct($pdo)
    {
        self::$pdo = $pdo;
        self::$logger = new BalooLogger();
    }

    /**
     * Method for loading libs dynamically.
     *
     * @static
     *
     * @param string $lib    Name (without suffix) of the library to be loaded, if null load all libraries found in specified folder (default=null)
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
                    require_once $path.'/'.$entry;
                }
                $d->close();
                unset($d);
            } else {
                require_once $path.'/'.$lib.'.php';
            }

            return true;
        } catch(\Exception $e) {
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
        return static::$rootDir.'/'.static::$folders[$key];
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
        return array_map(function ($folder) { return static::$rootDir.'/'.$folder; }, self::$folders);
    }
}
