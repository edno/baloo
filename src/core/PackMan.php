<?php
namespace Baloo\Packman;

/**
 * class Packman
 * Class that manages datasource packages
 *
 * @package Packman
 */

use Baloo\DataSourceManager;
use Baloo\DataSource;
use Baloo\BalooContext;
use Baloo\DataEntityType;

// @codingStandardsIgnoreStart
BalooContext::loadLibrary('arrays'); // workaround for non-class namespace
// @codingStandardsIgnoreEnd
use Baloo\Lib\Arrays;

class PackMan
{
    use \Baloo\Singleton;

    const NAME = 'BALOO PACKage MANager';
    const VERSION = '0.20160410';

    const JSON_EXT = '.pack.json';
    const GZIP_EXT = '.pack.json.gz';

    private static $packPath = __DIR__.'/../../public/packs/';

    protected static $pdo = null;
    protected static $dsManager = null;

    private function __init()
    {
        // instanciate PDO
        if (is_null(static::$pdo)) {
            static::$pdo = BalooContext::getInstance()->getPDO();
        }

        // instanciate DataSource manager
        if (is_null(static::$dsManager)) {
            static::$dsManager = DataSourceManager::getInstance();
        }
    }

    public function listPackFile($bNotInstalledOnly = false)
    {
        // list package available or installed
    }

    public function isPackInstalled(string $strPack)
    {
        // check if the pack is already installed
    }

    public function loadPackFile(string $strPack)
    {
        try {
            $packfile = $this->__getPackFile($strPack);
            $ext = pathinfo($packfile, PATHINFO_EXTENSION);
            if (preg_match('/gz/i', $ext)) {
                ob_start();
                readgzfile($packfile);
                $jsonPack = ob_get_contents();
                ob_end_clean();
            } else {
                $jsonPack = file_get_contents($packfile);
            }

            $pack = new Package($jsonPack);

            return $pack;
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function __getPackFile(string $strPack)
    {
        if (is_readable(self::$packPath.$strPack.self::JSON_EXT)) {
            return self::$packPath.$strPack.self::JSON_EXT;
        } elseif (is_readable(self::$packPath.$strPack.self::GZIP_EXT)) {
            return self::$packPath.$strPack.self::GZIP_EXT;
        } else {
            throw new PackManException('Package "'. $strPack .'" not accessible in directory "'. self::$packPath .'"');
        }
    }

    public function installPack(Package $pack, string $name = null)
    {
        $result = false;

        try {
            if (isset($pack->datasource)) {
                if (static::$dsManager->getDataSourceByName($pack->datasource->name) !== false) {
                    throw new PackManException('Datasource "'. $pack->datasource->name .'" already exists.', 100);
                } else {
                    $result = $this->__createDataSourceFromPack($pack->datasource, $type) || $result;
                }
            }
        } catch (Exception $e) {
            throw $e;
        }

        return (bool)$result;
    }

    public function dumpPack(string $name)
    {
        //todo
    }

    public function removePack(object $pack, $removeDSType = true)
    {
        $result = false;
        if (is_null($pack) === false) {
            if (static::$dsManager->getDataSourceByName($pack->datasource->name) === false) {
                throw new PackManException('Datasource "'. $pack->datasource->name .'" doesn\'t exist.', 200);
            } else {
                static::$pdo->beginTransaction();

                static::$dsManager->deleteEntityProperties($pack->datasource->name);
                static::$dsManager->deleteEntityTypes($pack->datasource->name);

                if ($removeDSType === true) {
                    static::$dsManager->deleteDataSourceType($pack->datasource->name);
                }
                static::$dsManager->deleteDataSource($pack->datasource->name);

                $result = static::$pdo->commit();
            }
        }

        return (bool)$result;
    }

    private function __createDataSourceFromPack($datasource, $type = null)
    {
        if (is_object($datasource)) {
            $ds = new DataSource($datasource->name);
            $ds->setVersion($datasource->version);
            $ds->save();
        } else {
            throw new PackManException('Invalid datasource object.');
        }

        static::$pdo->beginTransaction();
        $propTypes = $this->__listPackPropertyTypes($datasource->entities);
        foreach ($propTypes as $type) {
            list($name, $format) = $type;
            static::$dsManager->insertDataTypeFieldType($name, $format);
        }
        $result = static::$pdo->commit();

        if ($result) {
            foreach ($datasource->entities as $type) {
                $typeID = static::$dsManager->insertDataType($ds->getId(), $type->name);
                static::$pdo->beginTransaction();
                foreach ($type->properties as $property) {
                    static::$dsManager->insertDataTypeField(
                        $typeID,
                        $property->name,
                        $property->type,
                        (isset($item->format)?$item->format:null),
                        (isset($item->custom)?$item->custom:0)
                    );
                }
                $result = static::$pdo->commit();
            }
        }

        return (bool)$result;
    }

    private function __listPackPropertyTypes($entities)
    {
        $list = array();
        // get complete list of properties
        foreach ($entities as $type) {
            $list = array_reduce(
                $type->properties,
                function ($result, $item) {
                    array_push(
                        $result,
                        array($item->type,
                        (isset($item->format)?$item->format:null))
                    );
                    return $result;
                },
                $list
            );
        }

        // reduce properties list
        $list = Arrays\array_unique_recursive($list);

        // avoid duplicate entries of type
        $existingTypes = DataEntityType::getPropertyTypesList();
        array_walk(
            $existingTypes,
            function (&$item, $key) {
                array_shift($item);
                $item = array_values($item);
            }
        ); // remove 'id' entry

        $list = Arrays\array_diff_recursive($list, $existingTypes);

        return $list;
    }
}
