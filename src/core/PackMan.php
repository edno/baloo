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

class Packman {

  const NAME = 'BALOO PACKage MANager';
  const VERSION = '0.20160410';
  const PATH = __DIR__.'/../../public/packs/';

  const JSON_EXT = '.pack.json';
  const GZIP_EXT = '.pack.json.gz';

  public function __construct() {
    //nothing
  }

  static public function readConsole() {
    return trim(fgets(STDIN));
  }

  static public function listPackFile($bNotInstalledOnly = false) {
    // list package available or installed
  }

  static public function isPackInstalled($strPack) {
    // check if the pack is already installed
  }

  static public function loadPackFile($strPack) {
    try {
      $packfile = self::_getPackFile($strPack);
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

  static private function _getPackFile($strPack) {
    if(is_readable(Packman::PATH.$strPack.Packman::JSON_EXT)) {
      return Packman::PATH.$strPack.Packman::JSON_EXT;
    }
    elseif(is_readable(Packman::PATH.$strPack.Packman::GZIP_EXT)) {
      return Packman::PATH.$strPack.Packman::GZIP_EXT;
    }
    else {
      throw new PackManException('Package "'. $strPack .'" not found in directory "'. Packman::PATH .'"');
    }
  }

  static public function installPack($pack, $name = null) {
    $result = false;

    try {
      if(isset($pack->datasourcetype)) {
        if((bool)DataSourceManager::getDataSourceTypeID($pack->datasourcetype->name) === false) {
          DataSourceManager::insertDataSourceType($pack->datasourcetype->name, $pack->datasourcetype->version); // if failed no consequence
        }
      }
      if(isset($pack->datasource)) {
        if(DataSourceManager::getDataSource($pack->datasource->name) !== false) {
          throw new PackManException('Datasource "'. $pack->datasource->name .'" already exists.', 100);
        }
        else {
          $result = self::_createDataSourceFromPack($pack->datasource) || $result;
        }
      }
    }
    catch(Exception $e) {
      throw $e;
    }

    return (bool)$result;
  }

  static public function dumpPack($name) {
    //todo
  }

  static public function removePack($pack, $removeDSType = true) {
    $result = false;
    if(is_null($pack) === false) {
      if(DataSourceManager::getDataSource($pack->datasource->name) === false) {
        throw new PackManException('Datasource "'. $pack->datasource->name .'" doesn\'t exist.', 200);
      }
      else {
        BalooContext::$pdo->beginTransaction();

        DataSourceManager::deleteEntityProperties($pack->datasource->name);
        DataSourceManager::deleteEntityTypes($pack->datasource->name);

        if($removeDSType === true) DataSourceManager::deleteDataSourceType($pack->datasource->name);
        DataSourceManager::deleteDataSource($pack->datasource->name);

        $result = BalooContext::$pdo->commit();
      }
    }

    return (bool)$result;
  }

  static private function _createDataSourceFromPack($datasource) {
    if(is_object($datasource)) {
      $dsID = DataSourceManager::insertDataSource($datasource->name, $datasource->version, $datasource->type);
    }
    else {
      throw new PackManException('Invalid datasource object.');
    }

    BalooContext::$pdo->beginTransaction();
    $propTypes = self::_listPackPropertyTypes($datasource->entities);
    foreach($propTypes as $type) {
      list($name, $format) = $type;
      DataSourceManager::insertDataTypeFieldType($name, $format);
    }
    $result = BalooContext::$pdo->commit();

    foreach($datasource->entities as $type) {
      $typeID = DataSourceManager::insertDataType($dsID, $type->name);
      BalooContext::$pdo->beginTransaction();
      foreach($type->properties as $property) {
        DataSourceManager::insertDataTypeField($typeID,
                                               $property->name,
                                               $property->type,
                                               (isset($item->format)?$item->format:null),
                                               (isset($item->custom)?$item->custom:0));
      }
      $result = BalooContext::$pdo->commit();
    }

    return (bool)$result;
  }

  static private function _listPackPropertyTypes($entities) {
    BalooContext::loadLibrary('arrays', 'lib'); // import extended arrays functions

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
    $list = array_unique_recursive($list);

    // avoid duplicate entries of type
    $existingTypes = DataEntityType::getPropertyTypesList();
    array_walk($existingTypes, function(&$item, $key) { array_shift($item); $item = array_values($item); }); // remove 'id' entry

    $list = array_diff_recursive($list, $existingTypes);

    return $list;
  }

}
