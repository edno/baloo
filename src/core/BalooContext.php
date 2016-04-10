<?php

namespace Baloo;

/**
 * class BalooContext
 * Class that manages global BALOO execution context (mandatory).
 */
class BalooContext
{
    public static $debug = false;

    private static $folders = array(
        'lib' => 'lib/',
        'core' => 'core/',
    );

    private static $ext = array(
        'lib' => '.lib.php',
        'core' => '.class.php',
    );

    public static $pdo = null;
    public static $tablePrefix = '_';
    public static $rootDir = null;

    public static $logger = null;

    public function __construct($pdo)
    {
        self::$pdo = $pdo;
        self::$rootDir = __DIR__;
        self::$logger = new BalooLogger();
    }

    /**
     * Method for loading libs dynamically.
     *
     * @static
     *
     * @param string $lib    Name (without suffix) of the library to be loaded, if null load all libraries found in specified folder (default=null)
     * @param string $folder Folder that contains librairies (default='lib')
     *
     * @return bool
     */
    public static function loadLibrary($lib = null, $folder = 'core')
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
                require_once $path.'/'.$lib.self::folderExt($folder);
            }

            return true;
        } catch (Exception $e) {
            echo 'ERROR: Failed to open library '.$lib.' in  folder '.$folder.'!'.PHP_EOL;

            return false;
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
        return array_map(function ($folder) { return BalooContext::$rootDir.'/'.$folder; }, self::$folders);
    }

    /**
     * Get folder's class file extension.
     *
     * @static
     *
     * @param string $key Folder's name to retreive path
     *
     * @return string Class file extension
     */
    public static function folderExt($key)
    {
        return static::$ext[$key];
    }
}
