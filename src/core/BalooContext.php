<?php

namespace Baloo;

/**
 * class BalooContext
 * Class that manages global BALOO execution context (mandatory).
 */

class BalooContext
{
    use Singleton;

    //public static $tablePrefix = '_';
    //public static $rootDir = __DIR__;

    private $logger = null;
    private $pdo = null;

    protected function __init()
    {
        $this->logger = new BalooLogger();
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
     * @return true if success
     */
    public static function loadLibrary($lib = null)
    {
        try {
            $path = dirname(__DIR__).'/lib';
            if (is_null($lib) === true) {
                foreach (new \DirectoryIterator($path) as $file) {
                    if ($file->isDot()) {
                        continue;
                    }
                    include_once $file->getRealPath();
                }
            } else {
                include_once $path.'/'.$lib.'.php';
            }
            return true;
        } catch (\Exception $e) {
            throw new BalooException("Failed to open library ${lib}!");
        }
    }

    public function setPDO(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getPDO()
    {
        if (get_class($this->pdo) === 'PDO') {
            return $this->pdo;
        } else {
            throw new BalooException("No valid PDO connection");
        }
    }

    public function getLogger()
    {
        return $this->logger;
    }
}
