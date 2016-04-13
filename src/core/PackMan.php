<?php
namespace Baloo\Packman;

/**
 * class Packman
 * Class that manages datasource packages
 *
 * @package Packman
 */

use Baloo\Packman\Package;
use Baloo\Packman\PackManException;

use Baloo\DataSourceManager;
use Baloo\BalooContext;
use Baloo\DataEntityType;

BalooContext::loadLibrary('arrays'); // workaround for non-class namespace
use Baloo\Lib\Arrays;

class Packman {
  use \Baloo\Singleton;

  const NAME = 'BALOO PACKage MANager';
  const VERSION = '0.20160410';

  const JSON_EXT = '.pack.json';
  const GZIP_EXT = '.pack.json.gz';

  private static $packPath = __DIR__.'/../../public/packs/';
  
  public function listPackFile($bNotInstalledOnly = false) {
    // list package available or installed
  }

  public function isPackInstalled(string $strPack) {
    // check if the pack is already installed
  }

  public function loadPackFile(string $strPack) {
    try {
      $packfile = $this->_getPackFile($strPack);
      $ext = pathinfo($packfile, PATHINFO_EXTENSION);
      if(preg_match('/gz/i', $ext)) {
        ob_start();
        readgzfile($packfile);
        $jsonPack = ob_get_contents();
        ob_end_clean();
      }
      else {
        $jsonPack = file_get_contents($packfile);
      }

      $pack = new Package($jsonPack);

      return $pack;
    }
    catch(Exception $e) {
      throw $e;
    }
  }

  private function _getPackFile(string $strPack) {
    if(is_readable(self::$packPath.$strPack.self::JSON_EXT)) {
      return self::$packPath.$strPack.self::JSON_EXT;
    }
    elseif(is_readable(self::$packPath.$strPack.self::GZIP_EXT)) {
      return self::$packPath.$strPack.self::GZIP_EXT;
    }
    else {
      throw new PackManException('Package "'. $strPack .'" not accessible in directory "'. self::$packPath .'"');
    }
  }

  public function installPack(Package $pack, string $name = null) {
    $result = false;

    try {
      if(isset($pack->datasourcetype)) {
        if((bool)DataSourceManager::getInstance()->getDataSourceTypeID($pack->datasourcetype->name) === false) {
          DataSourceManager::getInstance()->insertDataSourceType($pack->datasourcetype->name, $pack->datasourcetype->version); // if failed no consequence
        }
      }
      if(isset($pack->datasource)) {
        if(DataSourceManager::getInstance()->getDataSource($pack->datasource->name) !== false) {
          throw new PackManException('Datasource "'. $pack->datasource->name .'" already exists.', 100);
        }
        else {
          $result = $this->_createDataSourceFromPack($pack->datasource) || $result;
        }
      }
    }
    catch(Exception $e) {
      throw $e;
    }

    return (bool)$result;
  }

  public function dumpPack(string $name) {
    //todo
  }

  public function removePack(object $pack, $removeDSType = true) {
    $result = false;
    if(is_null($pack) === false) {
      if(DataSourceManager::getInstance()->getDataSource($pack->datasource->name) === false) {
        throw new PackManException('Datasource "'. $pack->datasource->name .'" doesn\'t exist.', 200);
      }
      else {
        BalooContext::getInstance()->getPDO()->beginTransaction();

        DataSourceManager::getInstance()->deleteEntityProperties($pack->datasource->name);
        DataSourceManager::getInstance()->deleteEntityTypes($pack->datasource->name);

        if($removeDSType === true) DataSourceManager::getInstance()->deleteDataSourceType($pack->datasource->name);
        DataSourceManager::getInstance()->deleteDataSource($pack->datasource->name);

        $result = BalooContext::getInstance()->getPDO()->commit();
      }
    }

    return (bool)$result;
  }

  private function _createDataSourceFromPack($datasource) {
    if(is_object($datasource)) {
      $dsID = DataSourceManager::getInstance()->insertDataSource($datasource->name, $datasource->version, $datasource->type);
    }
    else {
      throw new PackManException('Invalid datasource object.');
    }

    BalooContext::getInstance()->getPDO()->beginTransaction();
    $propTypes = $this->_listPackPropertyTypes($datasource->entities);
    foreach($propTypes as $type) {
      list($name, $format) = $type;
      DataSourceManager::getInstance()->insertDataTypeFieldType($name, $format);
    }
    $result = BalooContext::getInstance()->getPDO()->commit();
    
    if($result) {
        foreach($datasource->entities as $type) {
          $typeID = DataSourceManager::getInstance()->insertDataType($dsID, $type->name);
          BalooContext::getInstance()->getPDO()->beginTransaction();
          foreach($type->properties as $property) {
            DataSourceManager::getInstance()->insertDataTypeField($typeID,
                                                   $property->name,
                                                   $property->type,
                                                   (isset($item->format)?$item->format:null),
                                                   (isset($item->custom)?$item->custom:0));
          }
          $result = BalooContext::getInstance()->getPDO()->commit();
      }
    }

    return (bool)$result;
  }

  private function _listPackPropertyTypes($entities) {
    $list = array();
    // get complete list of properties
    foreach($entities as $type) {
      $list = array_reduce(
          $type->properties,
          function($result, $item) { array_push($result,
                                                array($item->type,
                                                (isset($item->format)?$item->format:null)));
                                     return $result; },
          $list);
    }

    // reduce properties list
    $list = Arrays\array_unique_recursive($list);

    // avoid duplicate entries of type
    $existingTypes = DataEntityType::getPropertyTypesList();
    array_walk($existingTypes, function(&$item, $key) { array_shift($item); $item = array_values($item); }); // remove 'id' entry

    $list = Arrays\array_diff_recursive($list, $existingTypes);

    return $list;
  }

}
