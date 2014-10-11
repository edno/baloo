<?php

/**
 * class DataSourceManager
 * Class that manages datasource records into database
 *
 * @package baloo
 */

// @ TODO include ability to INSERT with IGNORE or UPDATE

// @ TODO check that only one INSERT transaction is used if several VALUES for the same TABLE

class DataSourceManager {

	static public function getDataSourceType($name) {
		$result = false;
		$datasource = self::getDataSource($name);
		if(is_object($datasource)) {
			$type = $datasource->getDataSourceType();
			$result = is_null($type) ? false : $type;
		}
		
		return $result;
	}
	
	static public function getDataSource($name) {
		 return DataSource::getDataSourceByName($name);
	}	

	static public function deleteEntityTypes($datasource_name = null) {
		$query = BalooContext::$pdo->prepare("
			DELETE FROM ". BalooModel::tableEntityType() ." 
			WHERE EXISTS (
				SELECT 1
				FROM ". BalooModel::tableDataSource() ." AS _SOURCE
				WHERE _SOURCE.name=:name AND _SOURCE.id=". BalooModel::tableEntityType() .".". BalooModel::tableDataSource() ."_id
				)
			");
		return $query->execute(array(':name' => $datasource_name));
	}
	
	static public function deleteEntityProperties($datasource_name = null) {
		$query = BalooContext::$pdo->prepare("
			DELETE
			FROM ". BalooModel::tableEntityField() ." 
			WHERE EXISTS(
				SELECT 1
				FROM ". BalooModel::tableEntityType() ." AS _TYPE
				INNER JOIN ". BalooModel::tableDataSource() ." AS _SOURCE
				ON _SOURCE.id = _TYPE.". BalooModel::tableDataSource() ."_id
				WHERE _SOURCE.name=:name AND _TYPE.id = ". BalooModel::tableEntityField() .".". BalooModel::tableEntityType() ."_id 
				)
			");
		return $query->execute(array(':name' => $datasource_name));
	}
	
	static public function deleteDataSource($datasource_name = null) {
		$query = BalooContext::$pdo->prepare("
				DELETE FROM ". BalooModel::tableDataSource() ." 
				WHERE name=:name
				");
		return $query->execute(array(':name' => $datasource_name));
	}
	
	static public function deleteDataSourceType($datasource_name = null) {
		$query = BalooContext::$pdo->prepare("
			DELETE FROM ". BalooModel::tableDataSourceType() ." 
			WHERE EXISTS(
				SELECT 1
				FROM ". BalooModel::tableDataSource() ." AS _SOURCE
				WHERE _SOURCE.name=:name AND ". BalooModel::tableDataSourceType() .".id=_SOURCE.". BalooModel::tableDataSourceType() ."_id
				)
			");
		return $query->execute(array(':name' => $datasource_name));
	}	
	
	static public function insertDataSourceType($name, $version) {
		$query = BalooContext::$pdo->prepare("
			INSERT INTO ". BalooModel::tableDataSourceType() ." (name, version)
			VALUES (:name, :version)
			");
		return $query->execute(array(':name' => $name, ':version' => $version));
	}
	
	static public function getDataSourceTypeId($name) {
		$query = BalooContext::$pdo->prepare("
			SELECT id
			FROM ". BalooModel::tableDataSourceType() ."
			WHERE name=:name
			");
		$query->execute(array(':name' => $name));
		return (integer)$query->fetchColumn();
	}	
	
	static public function insertDataSource($name, $version = null, $type = null) {
		if(is_null($type) === false) {
			$type = self::getDataSourceTypeId($type);
		}
		$query = BalooContext::$pdo->prepare("
			INSERT INTO ". BalooModel::tableDataSource() ." (name, version, ". BalooModel::tableDataSourceType() ."_id)
			VALUES (:name, :version, :type_id)
			");
		$query->execute(array(':name' => $name, ':version' => $version, ':type_id' => (integer)$type));	
		return BalooContext::$pdo->lastInsertId();
	}
	
	static public function insertDataType($datasource, $name) {
		$query = BalooContext::$pdo->prepare("
			INSERT INTO ". BalooModel::tableEntityType() ." (name, ". BalooModel::tableDataSource() ."_id)
			VALUES (:name, :datasource_id)
			");
		$query->execute(array(':name' => $name, ':datasource_id' => $datasource));		
		return BalooContext::$pdo->lastInsertId(); 
	}
	
	static public function insertDataTypeField($type, $name, $typefield = 0, $custom = 0) {
		$typefield = DataEntityType::getTypePropertyId($typefield);
		$query = BalooContext::$pdo->prepare("
			INSERT INTO ". BalooModel::tableEntityField() ." (name, custom, ". BalooModel::tableEntityType() ."_id, ". BalooModel::tableEntityFieldType() ."_id)
			VALUES (:name, :custom, :entitytype_id, :entityfieldtype_id)
			");
		return $query->execute(array(':name' => $name, ':custom' => (bool)$custom, ':entitytype_id' => (integer)$type, 'entityfieldtype_id' => (integer)$typefield));	
	}
	
	static public function insertDataTypeFieldType($name, $format = null) {
		$query = BalooContext::$pdo->prepare("
			INSERT INTO ". BalooModel::tableEntityFieldType() ." (name, format)
			VALUES (:name, :format)
			");
		return $query->execute(array(':name' => $name, ':format' => $format));	
	}

}

?>