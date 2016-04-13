<?php

namespace Baloo;

/**
 * class DataSource
 * Class that implements datasource (logical group of data).
 */
class DataSource
{
    protected $id = null;
    protected $name = null;
    protected $type = null;

    /**
     * Constructor must be accessed with method getDataSourceByName.
     *
     * @see getDataSourceByName
     */
    private function __construct()
    {
        $this->id = intval($this->id); // force id as integer
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getID()
    {
        return $this->id;
    }

    /**
     * Give access to DataSource constructor thru datasource's name.
     *
     * @static
     *
     * @param string $name Datasource name to get
     *
     * @return DataSource|false DataSource object or error
     */
    public static function getDataSourceByName($name)
    {
        $query = BalooContext::getInstance()->getPDO()->prepare('
        SELECT _SOURCE.id AS id, _SOURCE.name AS name, _SOURCE_TYPE.name AS type
        FROM '.BalooModel::tableDataSource().' AS _SOURCE
        INNER JOIN '.BalooModel::tableDataSourceType().' AS _SOURCE_TYPE
        ON _SOURCE_TYPE.id = _SOURCE.'.BalooModel::tableDataSourceType()."_id
        WHERE _SOURCE.name='".$name."'"
        );
        $query->setFetchMode(\PDO::FETCH_CLASS, 'DataSource');
        $query->execute();

        return $query->fetch(\PDO::FETCH_CLASS);
    }

    /**
     * Give the list of existing datasources.
     *
     * @static
     *
     * @return array|false array[id,name] of datasource or error
     */
    public static function getDataSourceList()
    {
        $query = BalooContext::getInstance()->getPDO()->prepare('
        SELECT id, name
        FROM '.BalooModel::tableDataSource()
        );
        $query->execute();

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Give the list of existing entity types for current datasource.
     *
     * @return array|false Array of DataEntityType objects or error
     */
    public function getEntityTypeList()
    {
        $query = BalooContext::getInstance()->getPDO()->query('
        SELECT _TYPE.id as id, _TYPE.name as name, _SOURCE.name AS datasourcename
        FROM '.BalooModel::tableEntityType().' AS _TYPE
        INNER JOIN '.BalooModel::tableDataSource().' AS _SOURCE
        ON _SOURCE.id=_TYPE.'.BalooModel::tableDataSource().'_id
        WHERE _TYPE.'.BalooModel::tableDataSource().'_id='.$this->id
        );
        $query->execute();

        return $query->fetchAll(\PDO::FETCH_CLASS, 'DataEntityType');
    }

    /**
     * Get specified entity type from current datasource.
     *
     * @param string $typeName Entity type name to get
     *
     * @return DataEntityType|false DataEntityType object or error
     */
    public function getEntityTypeByName($typeName)
    {
        $query = BalooContext::getInstance()->getPDO()->prepare('
        SELECT _TYPE.id as id, _TYPE.name as name, _SOURCE.name AS datasourcename
        FROM '.BalooModel::tableEntityType().' AS _TYPE
        INNER JOIN '.BalooModel::tableDataSource().' AS _SOURCE
        ON _SOURCE.id=_TYPE.'.BalooModel::tableDataSource().'_id
        WHERE _TYPE.'.BalooModel::tableDataSource().'_id='.$this->id."
        AND _TYPE.name='".$typeName."'"
        );
        $query->setFetchMode(\PDO::FETCH_CLASS, 'DataEntityType');
        $query->execute();

        return $query->fetch(\PDO::FETCH_CLASS);
    }

    /**
     * Give the list of existing entities of any type for current datasource.
     *
     * @param bool $excludeChildObject Set if child entities are exclude from list (default=true)
     *
     * @return array|false Array of types and their entities or error
     */
    public function getEntityList($excludeChildObject = true)
    {
        $results = $this->getEntityTypeList();
        if ($results) {
            array_walk($results, function (&$item, $key, $excludeChildObject) {
                $item = array('type' => $item, 'data' => $item->getEntityList($excludeChildObject));
            }, $excludeChildObject);
        }

        return $results;
    }

    /**
     * Give the list of existing entities of specified type for current datasource.
     *
     * @param string $typeName           Entity type name used to retrieve entities
     * @param bool   $excludeChildObject Set if child entities are exclude from returned list (default=true)
     *
     * @return array|false array[DataEntity] of DataEntity objects or error
     */
    public function getEntityListByType($typeName, $excludeChildObject = true)
    {
        return $this->getEntityTypeByName($typeName)->getEntityList($excludeChildObject);
    }

    public function getDataSourceType()
    {
        return $this->type;
    }
}
